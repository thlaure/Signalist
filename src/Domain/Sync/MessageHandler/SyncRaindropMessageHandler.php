<?php

declare(strict_types=1);

namespace App\Domain\Sync\MessageHandler;

use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Connection\Service\AccountTokens;
use App\Domain\Sync\DTO\RemoteBookmark;
use App\Domain\Sync\Message\SyncRaindropMessage;
use App\Domain\Sync\Port\RaindropClientInterface;
use App\Domain\Sync\Service\SyncConflictResolver;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\BookmarkSyncRun;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\RaindropLink;
use App\Entity\User;

use function array_slice;
use function count;

use Doctrine\ORM\EntityManagerInterface;

use const FILTER_VALIDATE_URL;

use function in_array;
use function is_array;
use function strlen;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class SyncRaindropMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private AccountTokens $tokens, private RaindropClientInterface $remote, private SyncConflictResolver $conflicts, private MessageBusInterface $bus, private UsageBudget $budget)
    {
    }

    public function __invoke(SyncRaindropMessage $message): void
    {
        $run = $this->entityManager->find(BookmarkSyncRun::class, Uuid::fromString($message->runId));

        if (!$run instanceof BookmarkSyncRun || 'pending' !== $run->getStatus()) {
            return;
        }
        $connection = $this->entityManager->getConnection();
        $key = 'raindrop-'.$run->getAccount()->getId()->toRfc4122();
        $locked = $connection->fetchOne('SELECT pg_try_advisory_lock(hashtextextended(:key, 0))', ['key' => $key]);

        if (!in_array($locked, [true, 1, 't'], true)) {
            return;
        }

        try {
            $this->entityManager->refresh($run);

            if ('pending' !== $run->getStatus()) {
                return;
            }
            $account = $run->getAccount();
            $this->entityManager->refresh($account);

            if ($account->getRemoteId() !== $run->getRemoteAccountId()) {
                $run->setStatus('cancelled');
                $this->entityManager->flush();

                return;
            }
            $token = $this->tokens->accessToken($account, $run->getRemoteAccountId());

            if ('import' === $run->getPhase()) {
                $this->importPage($run, $token);
            } else {
                $this->exportPage($run, $token);
            }
            $this->entityManager->flush();

            if ('pending' === $run->getStatus()) {
                $this->bus->dispatch(new SyncRaindropMessage($message->runId));
            }
        } catch (UnrecoverableMessageHandlingException|PlanLimitReachedException) {
            if ('uncertain' !== $run->getStatus()) {
                $run->setStatus('failed');
                $this->entityManager->flush();
            }

            throw new UnrecoverableMessageHandlingException('Bookmark sync failed. Check the connection and plan limits before restarting.');
        } finally {
            $connection->executeQuery('SELECT pg_advisory_unlock(hashtextextended(:key, 0))', ['key' => $key]);
        }
    }

    private function importPage(BookmarkSyncRun $run, string $token): void
    {
        $items = $this->remote->page($token, $run->getPage());
        foreach (array_slice($items, $run->getOffset(), 5) as $item) {
            $this->import($run, $item, $token);
            $run->increment();
            $run->advanceOffset();
            $this->entityManager->flush();
        }

        if ($run->getOffset() >= count($items)) {
            if (count($items) < 50) {
                $run->exportPhase();
            } else {
                $run->nextPage();
            }
        }
    }

    private function import(BookmarkSyncRun $run, RemoteBookmark $remote, string $token): void
    {
        if (!$this->safeUrl($remote->url)) {
            return;
        }
        $account = $run->getAccount();
        $owner = $account->getOwner();
        $mapping = $this->entityManager->getRepository(RaindropLink::class)->findOneBy(['account' => $account, 'remoteAccountId' => $run->getRemoteAccountId(), 'remoteId' => $remote->id]);

        if ($mapping instanceof RaindropLink) {
            $article = $mapping->getArticle();
            $bookmark = $this->entityManager->getRepository(Bookmark::class)->findOneBy(['article' => $article]);

            // Local removals stay local. Sync does not silently recreate a removed bookmark or delete remote data.
            if (!$bookmark instanceof Bookmark) {
                return;
            }
            $localHash = $this->localHash($bookmark, $run->includesNotes());
            $remoteHash = $this->conflicts->hash($remote->url, $remote->title, $run->includesNotes() ? $remote->note : null);
            $direction = $run->includesNotes() !== $mapping->includesNotes()
                ? ($run->includesNotes() && null !== $bookmark->getNotes() ? 'local' : 'remote')
                : $this->conflicts->direction($mapping->getLocalHash(), $mapping->getRemoteHash(), $localHash, $remoteHash);

            if ('local' === $direction) {
                $remote = $this->remote->update($token, $remote->id, $article->getUrl(), $article->getTitle(), $run->includesNotes() ? ($bookmark->getNotes() ?? '') : null);
            } elseif ('remote' === $direction) {
                if ('raindrop' === $article->getFeed()->getSource()) {
                    $article->setTitle(mb_substr($remote->title, 0, 500))->setUrl($remote->url);
                }

                if ($run->includesNotes()) {
                    $bookmark->setNotes($remote->note);
                }
            }
        } else {
            $article = $this->entityManager->createQueryBuilder()->select('a')->from(Article::class, 'a')->join('a.feed', 'f')->where('f.owner = :owner')->andWhere('f.organization IS NULL')->andWhere('a.url = :url')
                ->setParameter('owner', $owner)->setParameter('url', $remote->url)->setMaxResults(1)->getQuery()->getOneOrNullResult();

            if (!$article instanceof Article) {
                if (!$this->budget->article($owner)) {
                    throw new PlanLimitReachedException('articles');
                }
                $article = new Article()->setFeed($this->importsFeed($owner))->setGuid('raindrop-'.$run->getRemoteAccountId().'-'.$remote->id)->setUrl($remote->url)->setTitle(mb_substr($remote->title, 0, 500));
                $this->entityManager->persist($article);
            }
            $bookmark = $this->entityManager->getRepository(Bookmark::class)->findOneBy(['article' => $article]);

            if (!$bookmark instanceof Bookmark) {
                $bookmark = new Bookmark()->setArticle($article)->setNotes($run->includesNotes() ? $remote->note : null);
                $this->entityManager->persist($bookmark);
            }
            $mapping = new RaindropLink($account, $article, $run->getRemoteAccountId(), $remote->id);
            $this->entityManager->persist($mapping);
        }
        $mapping->snapshot($this->localHash($bookmark, $run->includesNotes()), $this->conflicts->hash($remote->url, $remote->title, $run->includesNotes() ? $remote->note : null), $run->includesNotes());
    }

    private function exportPage(BookmarkSyncRun $run, string $token): void
    {
        /** @var list<Bookmark> $bookmarks */
        $bookmarks = $this->entityManager->createQueryBuilder()->select('b')->from(Bookmark::class, 'b')->join('b.article', 'a')->join('a.feed', 'f')->where('f.owner = :owner')->andWhere('f.organization IS NULL')
            ->setParameter('owner', $run->getAccount()->getOwner())->orderBy('b.id', 'ASC')->setFirstResult($run->getOffset())->setMaxResults(5)->getQuery()->getResult();
        foreach ($bookmarks as $bookmark) {
            $article = $bookmark->getArticle();
            $mapping = $this->entityManager->getRepository(RaindropLink::class)->findOneBy(['account' => $run->getAccount(), 'remoteAccountId' => $run->getRemoteAccountId(), 'article' => $article]);

            if (!$mapping instanceof RaindropLink && $this->safeUrl($article->getUrl())) {
                // A lost POST response has an uncertain outcome. Persist this before sending and never blindly retry a create.
                $run->setStatus('uncertain');
                $this->entityManager->flush();
                $remote = $this->remote->create($token, $article->getUrl(), $article->getTitle(), $run->includesNotes() ? ($bookmark->getNotes() ?? '') : null);
                $mapping = new RaindropLink($run->getAccount(), $article, $run->getRemoteAccountId(), $remote->id);
                $mapping->snapshot($this->localHash($bookmark, $run->includesNotes()), $this->conflicts->hash($remote->url, $remote->title, $run->includesNotes() ? $remote->note : null), $run->includesNotes());
                $this->entityManager->persist($mapping);
                $this->entityManager->flush();
                $run->setStatus('pending');
            }
            $run->advanceOffset();
            $run->increment();
            $this->entityManager->flush();
        }

        if (count($bookmarks) < 5) {
            $run->setStatus('complete');
            $run->getAccount()->synced();
        }
    }

    private function localHash(Bookmark $bookmark, bool $notes): string
    {
        return $this->conflicts->hash($bookmark->getArticle()->getUrl(), $bookmark->getArticle()->getTitle(), $notes ? $bookmark->getNotes() : null);
    }

    private function importsFeed(User $user): Feed
    {
        $feed = $this->entityManager->getRepository(Feed::class)->findOneBy(['owner' => $user, 'source' => 'raindrop', 'organization' => null]);

        if ($feed instanceof Feed) {
            return $feed;
        }
        $category = $this->entityManager->getRepository(Category::class)->findOneBy(['owner' => $user, 'slug' => 'raindrop-imports', 'organization' => null]);

        if (!$category instanceof Category) {
            $category = new Category()->setOwner($user)->setName('Raindrop.io')->setSlug('raindrop-imports');
            $this->entityManager->persist($category);
        }
        $feed = new Feed()->setOwner($user)->setCategory($category)->setTitle('Raindrop.io')->setUrl('https://api.raindrop.io/rest/v1/raindrops/0')->setSource('raindrop')->setStatus(Feed::STATUS_PAUSED);
        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        return $feed;
    }

    private function safeUrl(string $url): bool
    {
        if (strlen($url) > 2048 || false === filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $parts = parse_url($url);

        return is_array($parts) && in_array($parts['scheme'] ?? '', ['http', 'https'], true) && !isset($parts['user']) && !isset($parts['pass']);
    }
}
