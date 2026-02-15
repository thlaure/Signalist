<?php

declare(strict_types=1);

namespace App\Domain\Feed\Command;

final readonly class AddFeedCommand
{
    public function __construct(
        public string $url,
        public string $categoryId,
        public string $ownerId,
        public ?string $title = null,
    ) {
    }
}
