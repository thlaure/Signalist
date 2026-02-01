<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Category;

use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Category $category): void
    {
        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    public function delete(Category $category): void
    {
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function find(string $id): ?Category
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(Category::class, Uuid::fromString($id));
    }

    public function findBySlug(string $slug): ?Category
    {
        return $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy(['slug' => $slug]);
    }

    /** @return Category[] */
    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(Category::class)
            ->findBy([], ['position' => 'ASC', 'name' => 'ASC']);
    }

    public function hasFeedsAssigned(string $categoryId): bool
    {
        if (!Uuid::isValid($categoryId)) {
            return false;
        }

        $count = $this->entityManager->createQueryBuilder()
            ->select('COUNT(f.id)')
            ->from(\App\Entity\Feed::class, 'f')
            ->where('f.category = :categoryId')
            ->setParameter('categoryId', Uuid::fromString($categoryId))
            ->getQuery()
            ->getSingleScalarResult();

        return (int) $count > 0;
    }
}
