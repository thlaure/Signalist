<?php

declare(strict_types=1);

namespace App\Domain\Feed\Query;

final readonly class ListFeedsQuery
{
    public function __construct(
        public string $ownerId,
    ) {
    }
}
