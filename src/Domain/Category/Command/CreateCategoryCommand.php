<?php

declare(strict_types=1);

namespace App\Domain\Category\Command;

final readonly class CreateCategoryCommand
{
    public function __construct(
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $color = null,
        public int $position = 0,
    ) {
    }
}
