<?php

declare(strict_types=1);

namespace App\Domain\Article\Handler;

use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\ListArticlesQuery;
use App\Domain\Article\Query\PaginatedArticlesResult;

use function max;

final readonly class ListArticlesHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(ListArticlesQuery $query): PaginatedArticlesResult
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

        $total = $this->articleRepository->countAll($filters);
        $items = $this->articleRepository->findAll($filters, $query->page, $query->limit);
        $pages = max(1, (int) ceil($total / $query->limit));

        return new PaginatedArticlesResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
            pages: $pages,
        );
    }
}
