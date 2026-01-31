<?php

declare(strict_types=1);

namespace App\Domain\Category\DTO\Output;

use App\Entity\Category;
use DateTimeInterface;

final readonly class CategoryOutput
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $color,
        public int $position,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Category $category): self
    {
        return new self(
            id: $category->getId()->toRfc4122(),
            name: $category->getName(),
            slug: $category->getSlug(),
            description: $category->getDescription(),
            color: $category->getColor(),
            position: $category->getPosition(),
            createdAt: $category->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $category->getUpdatedAt()->format(DateTimeInterface::ATOM),
        );
    }
}
