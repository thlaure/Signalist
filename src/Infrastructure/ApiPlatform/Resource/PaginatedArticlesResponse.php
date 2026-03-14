<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

final readonly class PaginatedArticlesResponse
{
    /**
     * @param ArticleResource[] $items
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
