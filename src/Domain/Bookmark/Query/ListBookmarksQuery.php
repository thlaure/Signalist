<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Query;

final readonly class ListBookmarksQuery
{
    public function __construct(
        public string $ownerId,
    ) {
    }
}
