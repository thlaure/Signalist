<?php

declare(strict_types=1);

namespace App\Domain\Article\Query;

final readonly class ListArticlesQuery
{
    public function __construct(
        public string $ownerId,
        public ?string $feedId = null,
        public ?string $categoryId = null,
        public ?bool $isRead = null,
    ) {
    }
}
