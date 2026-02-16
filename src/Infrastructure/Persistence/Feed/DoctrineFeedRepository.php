<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Feed;

use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Feed;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineFeedRepository implements FeedRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Feed $feed): void
    {
        $this->entityManager->persist($feed);
        $this->entityManager->flush();
    }

    public function delete(Feed $feed): void
    {
        $this->entityManager->remove($feed);
        $this->entityManager->flush();
    }

    public function find(string $id): ?Feed
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(Feed::class, Uuid::fromString($id));
    }

    public function findByUrlAndOwner(string $url, string $ownerId): ?Feed
    {
        if (!Uuid::isValid($ownerId)) {
            return null;
        }

        return $this->entityManager
            ->getRepository(Feed::class)
            ->findOneBy(['url' => $url, 'owner' => Uuid::fromString($ownerId)]);
    }

    /** @return Feed[] */
    public function findAllByOwner(string $ownerId): array
    {
        if (!Uuid::isValid($ownerId)) {
            return [];
        }

        return $this->entityManager
            ->getRepository(Feed::class)
            ->findBy(['owner' => Uuid::fromString($ownerId)], ['title' => 'ASC']);
    }

    /** @return Feed[] */
    public function findByCategoryAndOwner(string $categoryId, string $ownerId): array
    {
        if (!Uuid::isValid($categoryId) || !Uuid::isValid($ownerId)) {
            return [];
        }

        return $this->entityManager
            ->getRepository(Feed::class)
            ->findBy(
                ['category' => Uuid::fromString($categoryId), 'owner' => Uuid::fromString($ownerId)],
                ['title' => 'ASC'],
            );
    }

    /** @return Feed[] */
    public function findActiveFeedsForCrawling(): array
    {
        return $this->entityManager
            ->getRepository(Feed::class)
            ->findBy(
                ['status' => Feed::STATUS_ACTIVE],
                ['lastFetchedAt' => 'ASC'],
            );
    }
}
