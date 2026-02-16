<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Persistence\Feed\DoctrineFeedRepository;
use App\Tests\Integration\DatabaseTestCase;

final class DoctrineFeedRepositoryTest extends DatabaseTestCase
{
    private DoctrineFeedRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DoctrineFeedRepository($this->entityManager);
    }

    public function testSaveAndFind(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $feed = $this->createFeed('Test Feed', 'https://example.com/feed', $category, $owner);

        $this->repository->save($feed);

        $found = $this->repository->find($feed->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame('Test Feed', $found->getTitle());
    }

    public function testFindReturnsNullForInvalidUuid(): void
    {
        $this->assertNull($this->repository->find('invalid'));
    }

    public function testFindByUrlAndOwner(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $feed = $this->createFeed('Feed', 'https://example.com/rss', $category, $owner);
        $this->repository->save($feed);

        $found = $this->repository->findByUrlAndOwner('https://example.com/rss', $owner->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame($feed->getId()->toRfc4122(), $found->getId()->toRfc4122());
    }

    public function testFindByUrlAndOwnerReturnsNullForDifferentOwner(): void
    {
        $owner1 = $this->createUser('user1@example.com');
        $owner2 = $this->createUser('user2@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner1);
        $feed = $this->createFeed('Feed', 'https://example.com/rss', $category, $owner1);
        $this->repository->save($feed);

        $this->assertNull($this->repository->findByUrlAndOwner('https://example.com/rss', $owner2->getId()->toRfc4122()));
    }

    public function testFindAllByOwner(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $feed1 = $this->createFeed('Zebra', 'https://a.com/feed', $category, $owner);
        $feed2 = $this->createFeed('Alpha', 'https://b.com/feed', $category, $owner);
        $this->repository->save($feed1);
        $this->repository->save($feed2);

        $otherOwner = $this->createUser('other@example.com');
        $otherCat = $this->createCategory('Other', 'other', $otherOwner);
        $otherFeed = $this->createFeed('Other', 'https://c.com/feed', $otherCat, $otherOwner);
        $this->repository->save($otherFeed);

        $result = $this->repository->findAllByOwner($owner->getId()->toRfc4122());

        $this->assertCount(2, $result);
        $this->assertSame('Alpha', $result[0]->getTitle());
    }

    public function testFindByCategoryAndOwner(): void
    {
        $owner = $this->createUser('test@example.com');
        $cat1 = $this->createCategory('Tech', 'tech', $owner);
        $cat2 = $this->createCategory('Biz', 'biz', $owner);
        $feed1 = $this->createFeed('Feed 1', 'https://a.com/feed', $cat1, $owner);
        $feed2 = $this->createFeed('Feed 2', 'https://b.com/feed', $cat2, $owner);
        $this->repository->save($feed1);
        $this->repository->save($feed2);

        $result = $this->repository->findByCategoryAndOwner($cat1->getId()->toRfc4122(), $owner->getId()->toRfc4122());

        $this->assertCount(1, $result);
        $this->assertSame('Feed 1', $result[0]->getTitle());
    }

    public function testDelete(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $feed = $this->createFeed('Feed', 'https://example.com/feed', $category, $owner);
        $this->repository->save($feed);

        $this->repository->delete($feed);

        $this->assertNull($this->repository->find($feed->getId()->toRfc4122()));
    }

    private function createUser(string $email): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setPassword('hashed');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    private function createCategory(string $name, string $slug, User $owner): Category
    {
        $category = new Category();
        $category->setName($name);
        $category->setSlug($slug);
        $category->setOwner($owner);
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    private function createFeed(string $title, string $url, Category $category, User $owner): Feed
    {
        $feed = new Feed();
        $feed->setTitle($title);
        $feed->setUrl($url);
        $feed->setCategory($category);
        $feed->setOwner($owner);

        return $feed;
    }
}
