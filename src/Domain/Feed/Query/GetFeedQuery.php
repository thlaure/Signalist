<?php

declare(strict_types=1);

namespace App\Domain\Feed\Query;

final readonly class GetFeedQuery
{
    public function __construct(
        public string $id,
        public string $ownerId,
    ) {
    }
}
