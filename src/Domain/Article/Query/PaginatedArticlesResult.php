<?php

declare(strict_types=1);

namespace App\Domain\Article\Query;

use App\Entity\Article;

final readonly class PaginatedArticlesResult
{
    /**
     * @param Article[] $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $page,
        public int $limit,
        public int $pages,
    ) {
    }
}
