<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Command;

final readonly class DeleteBookmarkCommand
{
    public function __construct(
        public string $id,
    ) {
    }
}
