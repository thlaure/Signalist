<?php

declare(strict_types=1);

namespace App\Domain\Category\Port;

use App\Entity\Category;

interface CategoryRepositoryInterface
{
    public function save(Category $category): void;

    public function delete(Category $category): void;

    public function find(string $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    /** @return Category[] */
    public function findAll(): array;

    public function hasFeedsAssigned(string $categoryId): bool;
}
