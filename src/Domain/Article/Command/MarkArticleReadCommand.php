<?php

declare(strict_types=1);

namespace App\Domain\Article\Command;

final readonly class MarkArticleReadCommand
{
    public function __construct(
        public string $id,
        public bool $isRead,
        public string $ownerId,
    ) {
    }
}
