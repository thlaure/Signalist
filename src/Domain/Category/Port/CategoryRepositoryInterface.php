<?php

declare(strict_types=1);

namespace App\Domain\Category\Port;

use App\Entity\Category;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function delete(Category $category): void;

    public function find(string $id): ?Category;

    public function findBySlugAndOwner(string $slug, string $ownerId): ?Category;

    /** @return Category[] */
    public function findAllByOwner(string $ownerId): array;

    public function hasFeedsAssigned(string $categoryId): bool;
}
