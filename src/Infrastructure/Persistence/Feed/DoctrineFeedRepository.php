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

    public function findByUrl(string $url): ?Feed
    {
        return $this->entityManager
            ->getRepository(Feed::class)
            ->findOneBy(['url' => $url]);
    }

    /** @return Feed[] */
    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(Feed::class)
            ->findBy([], ['title' => 'ASC']);
    }

    /** @return Feed[] */
    public function findByCategory(string $categoryId): array
    {
        if (!Uuid::isValid($categoryId)) {
            return [];
        }

        return $this->entityManager
            ->getRepository(Feed::class)
            ->findBy(
                ['category' => Uuid::fromString($categoryId)],
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
