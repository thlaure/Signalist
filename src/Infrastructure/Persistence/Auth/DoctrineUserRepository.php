<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Auth;

use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function find(string $id): ?User
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(User::class, Uuid::fromString($id));
    }

    public function findByEmail(string $email): ?User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }
}
