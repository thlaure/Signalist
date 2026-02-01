<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;

final readonly class ListCategoriesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    /**
     * @return Category[]
     */
    public function __invoke(): array
    {
        return $this->categoryRepository->findAll();
    }
}
