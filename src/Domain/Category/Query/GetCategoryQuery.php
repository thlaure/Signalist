<?php

declare(strict_types=1);

namespace App\Domain\Category\Query;

final readonly class GetCategoryQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
