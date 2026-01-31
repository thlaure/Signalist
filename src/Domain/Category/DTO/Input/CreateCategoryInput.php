<?php

declare(strict_types=1);

namespace App\Domain\Category\DTO\Input;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCategoryInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100)]
        public string $name,

        #[Assert\NotBlank]
        #[Assert\Length(min: 1, max: 100)]
        #[Assert\Regex(pattern: '/^[a-z0-9-]+$/', message: 'Slug must contain only lowercase letters, numbers, and hyphens.')]
        public string $slug,

        #[Assert\Length(max: 1000)]
        public ?string $description = null,

        #[Assert\Regex(pattern: '/^#[0-9A-Fa-f]{6}$/', message: 'Color must be a valid hex color (e.g., #FF5733).')]
        public ?string $color = null,

        #[Assert\PositiveOrZero]
        public int $position = 0,
    ) {
    }
}
