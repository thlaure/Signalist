<?php

declare(strict_types=1);

namespace App\Domain\Article\Handler;

use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\ListArticlesQuery;
use App\Entity\Article;

final readonly class ListArticlesHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    /**
     * @return Article[]
     */
    public function __invoke(ListArticlesQuery $query): array
    {
        $filters = ['ownerId' => $query->ownerId];

        if ($query->feedId !== null) {
            $filters['feedId'] = $query->feedId;
        }

        if ($query->categoryId !== null) {
            $filters['categoryId'] = $query->categoryId;
        }

        if ($query->isRead !== null) {
            $filters['isRead'] = $query->isRead;
        }

        if ($query->search !== null) {
            $filters['search'] = $query->search;
        }

        return $this->articleRepository->findAll($filters);
    }
}
