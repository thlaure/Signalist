<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\Persistence\Category\DoctrineCategoryRepository;
use App\Tests\Integration\DatabaseTestCase;

final class DoctrineCategoryRepositoryTest extends DatabaseTestCase
{
    private DoctrineCategoryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DoctrineCategoryRepository($this->entityManager);
    }

    public function testSaveAndFind(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);

        $this->repository->save($category);

        $found = $this->repository->find($category->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame('Tech', $found->getName());
        $this->assertSame('tech', $found->getSlug());
    }

    public function testFindReturnsNullForInvalidUuid(): void
    {
        $this->assertNull($this->repository->find('not-a-uuid'));
    }

    public function testFindReturnsNullForNonexistent(): void
    {
        $this->assertNull($this->repository->find('01961234-5678-7abc-8000-000000000000'));
    }

    public function testFindBySlugAndOwner(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $this->repository->save($category);

        $found = $this->repository->findBySlugAndOwner('tech', $owner->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame($category->getId()->toRfc4122(), $found->getId()->toRfc4122());
    }

    public function testFindBySlugAndOwnerReturnsNullForDifferentOwner(): void
    {
        $owner1 = $this->createUser('user1@example.com');
        $owner2 = $this->createUser('user2@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner1);
        $this->repository->save($category);

        $this->assertNull($this->repository->findBySlugAndOwner('tech', $owner2->getId()->toRfc4122()));
    }

    public function testFindAllByOwner(): void
    {
        $owner = $this->createUser('test@example.com');
        $cat1 = $this->createCategory('Alpha', 'alpha', $owner);
        $cat1->setPosition(1);
        $cat2 = $this->createCategory('Beta', 'beta', $owner);
        $cat2->setPosition(0);
        $this->repository->save($cat1);
        $this->repository->save($cat2);

        $otherOwner = $this->createUser('other@example.com');
        $otherCat = $this->createCategory('Other', 'other', $otherOwner);
        $this->repository->save($otherCat);

        $result = $this->repository->findAllByOwner($owner->getId()->toRfc4122());

        $this->assertCount(2, $result);
        $this->assertSame('Beta', $result[0]->getName());
        $this->assertSame('Alpha', $result[1]->getName());
    }

    public function testDelete(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $this->repository->save($category);

        $this->repository->delete($category);

        $this->assertNull($this->repository->find($category->getId()->toRfc4122()));
    }

    public function testHasFeedsAssigned(): void
    {
        $owner = $this->createUser('test@example.com');
        $category = $this->createCategory('Tech', 'tech', $owner);
        $this->repository->save($category);

        $this->assertFalse($this->repository->hasFeedsAssigned($category->getId()->toRfc4122()));

        $feed = new Feed();
        $feed->setTitle('Test Feed');
        $feed->setUrl('https://example.com/feed');
        $feed->setCategory($category);
        $feed->setOwner($owner);
        $this->entityManager->persist($feed);
        $this->entityManager->flush();

        $this->assertTrue($this->repository->hasFeedsAssigned($category->getId()->toRfc4122()));
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

        return $category;
    }
}
