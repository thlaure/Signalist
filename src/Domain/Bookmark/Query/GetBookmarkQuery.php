<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Query;

final readonly class GetBookmarkQuery
{
    public function __construct(
        public string $id,
    ) {
    }
}
