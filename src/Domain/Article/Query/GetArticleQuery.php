<?php

declare(strict_types=1);

namespace App\Domain\Article\Query;

final readonly class GetArticleQuery
{
    public function __construct(
        public string $id,
        public string $ownerId,
    ) {
    }
}
