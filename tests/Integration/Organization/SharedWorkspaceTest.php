<?php

declare(strict_types=1);

namespace App\Tests\Integration\Organization;

use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\DTO\Input\UpdateCategoryInput;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\DTO\Input\UpdateFeedInput;
use App\Domain\Organization\Handler\OrganizationHandler;
use App\Domain\Organization\Handler\SharedWorkspaceHandler;
use App\Domain\Organization\Service\OrganizationPolicy;
use App\Entity\Article;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Infrastructure\Organization\InvitationToken;
use App\Infrastructure\Persistence\Article\DoctrineArticleRepository;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class SharedWorkspaceTest extends DatabaseTestCase
{
    private function user(string $email): User
    {
        $user = new User()->setEmail($email)->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /** @return array{OrganizationHandler, SharedWorkspaceHandler} */
    private function handlers(): array
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
        $organizations = new OrganizationHandler($this->entityManager, new OrganizationPolicy(), new InvitationToken('test-secret', 'http://localhost'), $bus);

        return [$organizations, new SharedWorkspaceHandler($this->entityManager, $organizations, $bus)];
    }

    public function testSharedCategoryFeedAndBookmarkLifecyclePreservesRoleBoundaries(): void
    {
        $owner = $this->user('owner@example.com');
        [$organizations, $workspace] = $this->handlers();
        $organization = $organizations->create($owner, 'Team');
        $id = $organization->getId()->toRfc4122();
        $category = $workspace->createCategory($owner, $id, new CreateCategoryInput('News', 'news'));
        $workspace->updateCategory($owner, $id, $category->getId()->toRfc4122(), new UpdateCategoryInput('Technology', 'technology'));
        $this->assertSame('Technology', $workspace->categories($owner, $id)[0]->name);
        $feed = $workspace->createFeed($owner, $id, new AddFeedInput('https://example.com/rss', $category->getId()->toRfc4122()));
        $workspace->updateFeed($owner, $id, $feed->getId()->toRfc4122(), new UpdateFeedInput('Updated feed', $category->getId()->toRfc4122(), 'paused'));
        $this->assertSame('paused', $workspace->feeds($owner, $id)[0]->status);

        try {
            $workspace->deleteCategory($owner, $id, $category->getId()->toRfc4122());
            $this->fail('Category with feeds deleted');
        } catch (ConflictHttpException $error) {
            $this->assertSame(409, $error->getStatusCode());
        }
        $article = new Article()->setFeed($feed)->setTitle('Useful shared source')->setGuid('guid')->setUrl('https://example.com/source');
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        $this->assertCount(1, $workspace->articles($owner, $id, 1, 'Useful'));
        $bookmark = $workspace->bookmark($owner, $id, $article->getId()->toRfc4122());
        $this->assertSame($bookmark, $workspace->bookmark($owner, $id, $article->getId()->toRfc4122()));
        $workspace->markRead($owner, $id, $article->getId()->toRfc4122(), true);
        $workspace->markRead($owner, $id, $article->getId()->toRfc4122(), false);
        $this->assertFalse($workspace->articleOutput($owner, $article)['isRead']);
        $workspace->removeBookmark($owner, $id, $bookmark->getId()->toRfc4122());
        $this->assertSame([], $workspace->bookmarks($owner, $id));
        $workspace->deleteFeed($owner, $id, $feed->getId()->toRfc4122());
        $ownerId = $owner->getId();
        $this->entityManager->clear();
        $owner = $this->entityManager->find(User::class, $ownerId);
        $this->assertInstanceOf(User::class, $owner);
        $workspace->deleteCategory($owner, $id, $category->getId()->toRfc4122());
        $this->assertSame([], $workspace->feeds($owner, $id));
        $this->assertSame([], $workspace->categories($owner, $id));
        $this->assertCount(1, $organizations->list($owner));
    }

    public function testSharedReadStateAndCurationAreScopedAndPersonalListsExcludeWorkspace(): void
    {
        $owner = $this->user('owner@example.com');
        $reader = $this->user('reader@example.com');
        [$organizations, $workspace] = $this->handlers();
        $organization = $organizations->create($owner, 'Team');
        $this->entityManager->persist(new OrganizationMember($organization, $reader, 'reader'));
        $this->entityManager->flush();
        $id = $organization->getId()->toRfc4122();
        $category = $workspace->createCategory($owner, $id, new CreateCategoryInput('Tech', 'tech'));
        $feed = $workspace->createFeed($owner, $id, new AddFeedInput('https://example.com/rss', $category->getId()->toRfc4122()));
        $article = new Article()->setFeed($feed)->setGuid('guid')->setTitle('Shared news')->setUrl('https://example.com/a');
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        $articleId = $article->getId()->toRfc4122();
        $workspace->markRead($reader, $id, $articleId, true);
        $this->assertTrue($workspace->articleOutput($reader, $article)['isRead']);
        $this->assertFalse($workspace->articleOutput($owner, $article)['isRead']);
        $this->assertSame([], new DoctrineArticleRepository($this->entityManager)->findAll(['ownerId' => $owner->getId()->toRfc4122()]));
        $bookmark = $workspace->bookmark($owner, $id, $articleId);
        $workspace->annotate($owner, $id, $bookmark->getId()->toRfc4122(), 'Team context');
        $this->assertCount(1, $workspace->bookmarks($reader, $id));
        $this->expectException(AccessDeniedException::class);
        $workspace->annotate($reader, $id, $bookmark->getId()->toRfc4122(), 'Reader cannot write');
    }

    public function testLastAdministratorCannotBeRemoved(): void
    {
        $owner = $this->user('owner@example.com');
        [$organizations] = $this->handlers();
        $organization = $organizations->create($owner, 'Team');
        $member = $organization->getMembers()->first();
        $this->assertInstanceOf(OrganizationMember::class, $member);
        $this->expectException(ConflictHttpException::class);
        $organizations->changeMember($owner, $organization->getId()->toRfc4122(), $member->getId()->toRfc4122(), 'reader');
    }

    public function testInvitationMustMatchVerifiedAccountAndRevocationRemovesAccess(): void
    {
        $owner = $this->user('owner@example.com');
        $invited = $this->user('invited@example.com');
        [$organizations] = $this->handlers();
        $organization = $organizations->create($owner, 'Team');
        $organization->setPlan('team', new DateTimeImmutable('+1 month'));
        $this->entityManager->flush();
        $invitation = $organizations->invite($owner, $organization->getId()->toRfc4122(), $invited->getEmail(), 'curator');
        $signature = new InvitationToken('test-secret', 'http://localhost')->sign($invitation);
        $organizations->accept($invited, $invitation->getId()->toRfc4122(), $signature);
        $organizations->accept($invited, $invitation->getId()->toRfc4122(), $signature);
        $this->assertSame('curator', $organization->roleFor($invited));
        $this->assertSame(2, $this->entityManager->getRepository(OrganizationMember::class)->count(['organization' => $organization]));
        $member = $this->entityManager->getRepository(OrganizationMember::class)->findOneBy(['organization' => $organization, 'user' => $invited]);
        $this->assertInstanceOf(OrganizationMember::class, $member);
        $organizations->changeMember($owner, $organization->getId()->toRfc4122(), $member->getId()->toRfc4122(), null);
        $this->expectException(NotFoundHttpException::class);
        $organizations->get($invited, $organization->getId()->toRfc4122());
    }

    public function testOtherWorkspaceCannotReadSharedArticle(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        [$organizations, $workspace] = $this->handlers();
        $first = $organizations->create($owner, 'First');
        $second = $organizations->create($other, 'Second');
        $category = $workspace->createCategory($owner, $first->getId()->toRfc4122(), new CreateCategoryInput('News', 'news'));
        $feed = $workspace->createFeed($owner, $first->getId()->toRfc4122(), new AddFeedInput('https://example.com/rss', $category->getId()->toRfc4122()));
        $article = new Article()->setFeed($feed)->setTitle('Private team news')->setGuid('guid')->setUrl('https://example.com/a');
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        $this->expectException(NotFoundHttpException::class);
        $workspace->article($other, $second->getId()->toRfc4122(), $article->getId()->toRfc4122());
    }
}
