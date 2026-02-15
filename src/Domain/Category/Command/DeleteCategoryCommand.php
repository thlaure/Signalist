<?php

declare(strict_types=1);

namespace App\Domain\Category\Command;

final readonly class DeleteCategoryCommand
{
    public function __construct(
        public string $id,
        public string $ownerId,
    ) {
    }
}
