<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Handler;

use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Domain\Billing\Service\SeatSyncService;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\ArticleReadState;
use App\Entity\ArticleSearch;
use App\Entity\Bookmark;
use App\Entity\BookmarkAnnotation;
use App\Entity\Category;
use App\Entity\DataAccessAudit;
use App\Entity\ExternalAccount;
use App\Entity\Feed;
use App\Entity\Newsletter;
use App\Entity\OAuthAttempt;
use App\Entity\OAuthSdkStore;
use App\Entity\Organization;
use App\Entity\OrganizationActivity;
use App\Entity\OrganizationInvitation;
use App\Entity\OrganizationMember;
use App\Entity\SharedBookmark;
use App\Entity\SocialRecap;
use App\Entity\Subscription;
use App\Entity\Tag;
use App\Entity\UsageCounter;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Entity\WaitlistEntry;

use const DATE_ATOM;

use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class PrivacyHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ConsentPolicy $consentPolicy,
        private UserPasswordHasherInterface $passwordHasher,
        private ?SeatSyncService $seatSync = null,
    ) {
    }

    /** @return array{id: string, email: string, displayName: string, processingRestricted: bool, consent: array<string, bool>} */
    public function profile(User $user): array
    {
        return [
            'id' => $user->getId()->toRfc4122(),
            'email' => $user->getEmail(),
            'displayName' => $user->getDisplayName(),
            'processingRestricted' => $user->isProcessingRestricted(),
            'consent' => array_combine(
                ['external_ai', 'newsletter', 'marketing'],
                array_map(fn (string $purpose): bool => $this->consentPolicy->allows($user, $purpose), ['external_ai', 'newsletter', 'marketing']),
            ),
        ];
    }

    public function consent(User $user, string $purpose, bool $granted): void
    {
        $this->entityManager->persist(new UserConsent($user, $purpose, $granted));
        $this->audit($user, $granted ? 'consent_granted' : 'consent_withdrawn');
    }

    public function restrict(User $user, bool $restricted): void
    {
        $user->setProcessingRestricted($restricted);
        $this->audit($user, $restricted ? 'processing_restricted' : 'processing_resumed');
    }

    public function updateProfile(User $user, string $displayName): void
    {
        $user->setDisplayName(trim($displayName));
        $this->audit($user, 'profile_updated');
    }

    /** @return array<string, mixed> */
    public function export(User $user): array
    {
        $articles = $this->entityManager->createQueryBuilder()->select('a')->from(Article::class, 'a')
            ->join('a.feed', 'f')->where('f.owner = :owner')->andWhere('f.organization IS NULL')->setParameter('owner', $user)->getQuery()->getResult();
        /** @var list<Article> $articles */
        $categories = $this->entityManager->getRepository(Category::class)->findBy(['owner' => $user, 'organization' => null]);
        $feeds = $this->entityManager->getRepository(Feed::class)->findBy(['owner' => $user, 'organization' => null]);
        $consents = $this->entityManager->getRepository(UserConsent::class)->findBy(['user' => $user], ['createdAt' => 'ASC']);
        $audits = $this->entityManager->getRepository(DataAccessAudit::class)->findBy(['user' => $user], ['createdAt' => 'ASC']);
        $result = [
            'version' => 1,
            'tags' => array_map(static fn (Tag $tag): string => $tag->getName(), $this->entityManager->getRepository(Tag::class)->findBy(['owner' => $user])),
            'searchHistory' => array_map(static fn (ArticleSearch $search): array => ['query' => $search->getQuery(), 'mode' => $search->getMode(), 'answer' => $search->getAnswer(), 'status' => $search->getStatus()], $this->entityManager->getRepository(ArticleSearch::class)->findBy(['owner' => $user])),
            'newsletters' => array_map(static fn (Newsletter $newsletter): array => ['title' => $newsletter->getTitle(), 'html' => $newsletter->getHtml(), 'readingMinutes' => $newsletter->getReadingMinutes(), 'intervalHours' => $newsletter->getIntervalHours()], $this->entityManager->getRepository(Newsletter::class)->findBy(['owner' => $user])),
            'publications' => $this->entityManager->createQuery('SELECT p.posts, p.status, p.publishedIds FROM App\\Entity\\Crosspost p JOIN p.account a WHERE a.owner = :owner')->setParameter('owner', $user)->getArrayResult(),
            'socialRecaps' => array_map(static fn (SocialRecap $recap): array => ['platform' => $recap->getPlatform(), 'posts' => $recap->getPosts()], $this->entityManager->getRepository(SocialRecap::class)->findBy(['owner' => $user])),
            'connections' => array_map(static fn (ExternalAccount $account): array => ['provider' => $account->getProvider(), 'status' => $account->getStatus(), 'remoteId' => $account->getRemoteId()], $this->entityManager->getRepository(ExternalAccount::class)->findBy(['owner' => $user])),
            'exportedAt' => new DateTimeImmutable()->format(DATE_ATOM),
            'profile' => $this->profile($user),
            'workspaceMemberships' => array_map(static fn (OrganizationMember $member): array => ['role' => $member->getRole()], $this->entityManager->getRepository(OrganizationMember::class)->findBy(['user' => $user])),
            'annotations' => array_map(static fn (BookmarkAnnotation $annotation): array => ['body' => $annotation->getBody(), 'createdAt' => $annotation->getCreatedAt()->format(DATE_ATOM)], $this->entityManager->getRepository(BookmarkAnnotation::class)->findBy(['author' => $user])),
            'categories' => array_map(static fn (Category $category): array => [
                'id' => $category->getId()->toRfc4122(), 'name' => $category->getName(),
                'slug' => $category->getSlug(), 'description' => $category->getDescription(),
                'color' => $category->getColor(), 'position' => $category->getPosition(),
            ], $categories),
            'feeds' => array_map(static fn (Feed $feed): array => [
                'id' => $feed->getId()->toRfc4122(), 'title' => $feed->getTitle(), 'url' => $feed->getUrl(),
                'categoryId' => $feed->getCategory()->getId()->toRfc4122(), 'status' => $feed->getStatus(),
            ], $feeds),
            'articles' => array_map(ArticleOutput::fromEntity(...), $articles),
            'bookmarks' => array_values(array_filter(array_map(static fn (Article $article): ?array => $article->getBookmark() instanceof Bookmark ? [
                'id' => $article->getBookmark()->getId()->toRfc4122(),
                'articleId' => $article->getId()->toRfc4122(), 'notes' => $article->getBookmark()->getNotes(),
            ] : null, $articles))),
            'consentHistory' => array_map(static fn (UserConsent $consent): array => [
                'purpose' => $consent->getPurpose(), 'granted' => $consent->isGranted(), 'createdAt' => $consent->getCreatedAt()->format(DATE_ATOM),
            ], $consents),
            'accessHistory' => array_map(static fn (DataAccessAudit $audit): array => [
                'action' => $audit->getAction(), 'createdAt' => $audit->getCreatedAt()->format(DATE_ATOM),
            ], $audits),
        ];
        $this->audit($user, 'data_exported');

        return $result;
    }

    public function delete(User $user, string $password): void
    {
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            throw new AccessDeniedException('Password confirmation failed.');
        }

        $this->entityManager->wrapInTransaction(function () use ($user): void {
            $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy(['owner' => $user]);

            if ($subscription instanceof Subscription && in_array($subscription->getStatus(), ['active', 'trialing'], true)) {
                throw new ConflictHttpException('Cancel your subscription before deleting your account.');
            }
            $this->eraseWorkspaceMemberships($user);
            // Feed deletion cascades to articles, embeddings, and bookmarks in PostgreSQL.
            foreach ([Tag::class, SocialRecap::class, ExternalAccount::class, OAuthAttempt::class, OAuthSdkStore::class, Newsletter::class, ArticleSearch::class, Feed::class, Category::class] as $class) {
                $scope = in_array($class, [Feed::class, Category::class], true) ? ' AND e.organization IS NULL' : '';
                $this->entityManager->createQuery('DELETE FROM '.$class.' e WHERE e.owner = :user'.$scope)->setParameter('user', $user)->execute();
            }
            foreach ([UserConsent::class, DataAccessAudit::class] as $class) {
                $this->entityManager->createQuery('DELETE FROM '.$class.' e WHERE e.user = :user')->setParameter('user', $user)->execute();
            }
            $this->entityManager->createQuery('DELETE FROM '.WaitlistEntry::class.' w WHERE w.email = :email')->setParameter('email', $user->getEmail())->execute();
            $this->entityManager->createQuery('DELETE FROM '.Subscription::class.' s WHERE s.owner = :user')->setParameter('user', $user)->execute();
            $this->entityManager->createQuery('DELETE FROM '.UsageCounter::class.' c WHERE c.scopeKey = :scope')->setParameter('scope', 'user-'.$user->getId()->toRfc4122())->execute();
            $user->setEmail('deleted-'.$user->getId()->toRfc4122().'@invalid.local');
            $user->setPassword(bin2hex(random_bytes(32)));
            $user->setDisplayName('');
            $user->setRoles([]);
            $user->setProcessingRestricted(true);
            $user->setEmailVerifiedAt(null);
            $user->setDeletedAt(new DateTimeImmutable());
        });
    }

    private function audit(User $user, string $action): void
    {
        $this->entityManager->persist(new DataAccessAudit($user, $action));
        $this->entityManager->flush();
    }

    private function eraseWorkspaceMemberships(User $user): void
    {
        /** @var list<Organization> $organizations */
        $organizations = $this->entityManager->createQuery('SELECT o FROM '.Organization::class.' o JOIN o.members m WHERE m.user = :user')->setParameter('user', $user)->getResult();
        foreach ($organizations as $organization) {
            $this->entityManager->refresh($organization, LockMode::PESSIMISTIC_WRITE);
            $others = [];
            $administrators = [];
            foreach ($organization->getMembers() as $member) {
                $this->entityManager->refresh($member, LockMode::PESSIMISTIC_WRITE);

                if ($member->getUser()->getId()->equals($user->getId())) {
                    continue;
                }
                $others[] = $member;

                if ('admin' === $member->getRole()) {
                    $administrators[] = $member;
                }
            }

            if ([] === $others) {
                $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy(['organization' => $organization]);

                if ($subscription instanceof Subscription && in_array($subscription->getStatus(), ['active', 'trialing'], true)) {
                    throw new ConflictHttpException('Cancel workspace billing before deleting your account.');
                }
                $this->entityManager->remove($organization);
                $this->entityManager->flush();
                continue;
            }

            if ([] === $administrators) {
                throw new ConflictHttpException('Transfer workspace administration before deleting your account.');
            }
            $this->seatSync?->queue($organization);
            foreach ([Category::class, Feed::class] as $class) {
                $this->entityManager->createQuery('UPDATE '.$class.' e SET e.owner = :successor WHERE e.owner = :user AND e.organization = :organization')
                    ->setParameter('successor', $administrators[0]->getUser())->setParameter('user', $user)->setParameter('organization', $organization)->execute();
            }
        }
        $this->entityManager->createQuery('DELETE FROM '.OrganizationMember::class.' m WHERE m.user = :user')->setParameter('user', $user)->execute();
        $this->entityManager->createQuery('DELETE FROM '.ArticleReadState::class.' r WHERE r.user = :user')->setParameter('user', $user)->execute();
        $this->entityManager->createQuery('DELETE FROM '.BookmarkAnnotation::class.' a WHERE a.author = :user')->setParameter('user', $user)->execute();
        $this->entityManager->createQuery('UPDATE '.SharedBookmark::class.' b SET b.curator = NULL WHERE b.curator = :user')->setParameter('user', $user)->execute();
        $this->entityManager->createQuery('UPDATE '.OrganizationActivity::class.' a SET a.actor = NULL WHERE a.actor = :user')->setParameter('user', $user)->execute();
        $this->entityManager->createQuery('DELETE FROM '.OrganizationInvitation::class.' i WHERE i.email = :email')->setParameter('email', mb_strtolower($user->getEmail()))->execute();
    }
}
