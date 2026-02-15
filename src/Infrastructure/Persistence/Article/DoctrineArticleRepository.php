<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Article;

use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;

final readonly class DoctrineArticleRepository implements ArticleRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Article $article): void
    {
        $this->entityManager->persist($article);
        $this->entityManager->flush();
    }

    public function find(string $id): ?Article
    {
        if (!Uuid::isValid($id)) {
            return null;
        }

        return $this->entityManager->find(Article::class, Uuid::fromString($id));
    }

    /**
     * @param array{feedId?: string, categoryId?: string, isRead?: bool, ownerId?: string} $filters
     *
     * @return Article[]
     */
    public function findAll(array $filters = []): array
    {
        $qb = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->join('a.feed', 'f')
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC');

        $this->applyFilters($qb, $filters);

        /** @var Article[] $result */
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @return Article[]
     */
    public function findByFeed(string $feedId): array
    {
        if (!Uuid::isValid($feedId)) {
            return [];
        }

        return $this->entityManager
            ->getRepository(Article::class)
            ->findBy(
                ['feed' => Uuid::fromString($feedId)],
                ['publishedAt' => 'DESC', 'createdAt' => 'DESC'],
            );
    }

    /**
     * @return Article[]
     */
    public function findUnreadByOwner(string $ownerId): array
    {
        if (!Uuid::isValid($ownerId)) {
            return [];
        }

        /** @var Article[] $result */
        $result = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from(Article::class, 'a')
            ->join('a.feed', 'f')
            ->where('f.owner = :ownerId')
            ->andWhere('a.isRead = :isRead')
            ->setParameter('ownerId', Uuid::fromString($ownerId))
            ->setParameter('isRead', false)
            ->orderBy('a.publishedAt', 'DESC')
            ->addOrderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }

    /**
     * @param array{feedId?: string, categoryId?: string, isRead?: bool, ownerId?: string} $filters
     */
    private function applyFilters(QueryBuilder $qb, array $filters): void
    {
        if (isset($filters['ownerId']) && Uuid::isValid($filters['ownerId'])) {
            $qb->andWhere('f.owner = :ownerId')
                ->setParameter('ownerId', Uuid::fromString($filters['ownerId']));
        }

        if (isset($filters['feedId']) && Uuid::isValid($filters['feedId'])) {
            $qb->andWhere('a.feed = :feedId')
                ->setParameter('feedId', Uuid::fromString($filters['feedId']));
        }

        if (isset($filters['categoryId']) && Uuid::isValid($filters['categoryId'])) {
            $qb->andWhere('f.category = :categoryId')
                ->setParameter('categoryId', Uuid::fromString($filters['categoryId']));
        }

        if (isset($filters['isRead'])) {
            $qb->andWhere('a.isRead = :isRead')
                ->setParameter('isRead', $filters['isRead']);
        }
    }
}
