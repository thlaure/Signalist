<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Command;

final readonly class CreateBookmarkCommand
{
    public function __construct(
        public string $articleId,
        public string $ownerId,
        public ?string $notes = null,
    ) {
    }
}
