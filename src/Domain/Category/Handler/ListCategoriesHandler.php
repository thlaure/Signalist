<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Category\Query\ListCategoriesQuery;
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
    public function __invoke(ListCategoriesQuery $query): array
    {
        return $this->categoryRepository->findAllByOwner($query->ownerId);
    }
}
