<?php

declare(strict_types=1);

namespace App\Tests\Integration\Persistence;

use App\Entity\User;
use App\Infrastructure\Persistence\Auth\DoctrineUserRepository;
use App\Tests\Integration\DatabaseTestCase;

final class DoctrineUserRepositoryTest extends DatabaseTestCase
{
    private DoctrineUserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new DoctrineUserRepository($this->entityManager);
    }

    public function testSaveAndFind(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('hashed_password');

        $this->repository->save($user);

        $found = $this->repository->find($user->getId()->toRfc4122());

        $this->assertNotNull($found);
        $this->assertSame('test@example.com', $found->getEmail());
    }

    public function testFindReturnsNullForInvalidUuid(): void
    {
        $this->assertNull($this->repository->find('invalid'));
    }

    public function testFindByEmail(): void
    {
        $user = new User();
        $user->setEmail('find@example.com');
        $user->setPassword('hashed');
        $this->repository->save($user);

        $found = $this->repository->findByEmail('find@example.com');

        $this->assertNotNull($found);
        $this->assertSame($user->getId()->toRfc4122(), $found->getId()->toRfc4122());
    }

    public function testFindByEmailReturnsNullForNonexistent(): void
    {
        $this->assertNull($this->repository->findByEmail('noone@example.com'));
    }
}
