<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Bookmark;

use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Bookmark;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineBookmarkRepository implements BookmarkRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Bookmark $bookmark): void
    {
        $this->entityManager->persist($bookmark);
        $this->entityManager->flush();
    }

    public function delete(Bookmark $bookmark): void
    {
        $this->entityManager->remove($bookmark);
        $this->entityManager->flush();
    }

    public function find(string $id): ?Bookmark
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(Bookmark::class, Uuid::fromString($id));
    }

    public function findByArticle(string $articleId): ?Bookmark
    {
        if (!Uuid::isValid($articleId)) {
            return null;
        }

        return $this->entityManager
            ->getRepository(Bookmark::class)
            ->findOneBy(['article' => Uuid::fromString($articleId)]);
    }

    /** @return Bookmark[] */
    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(Bookmark::class)
            ->findBy([], ['createdAt' => 'DESC']);
    }
}
