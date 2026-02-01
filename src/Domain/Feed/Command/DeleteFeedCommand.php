<?php

declare(strict_types=1);

namespace App\Domain\Feed\Command;

final readonly class DeleteFeedCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
