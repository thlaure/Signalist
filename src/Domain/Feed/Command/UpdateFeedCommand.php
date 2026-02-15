<?php

declare(strict_types=1);

namespace App\Domain\Feed\Command;

final readonly class UpdateFeedCommand
{
    public function __construct(
        public string $id,
        public string $title,
        public string $categoryId,
        public string $status,
        public string $ownerId,
    ) {
    }
}
