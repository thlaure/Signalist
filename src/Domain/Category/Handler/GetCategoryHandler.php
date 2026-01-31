<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Entity\Category;

final readonly class GetCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(GetCategoryQuery $query): Category
    {
        $category = $this->categoryRepository->find($query->id);

        if ($category === null) {
            throw new CategoryNotFoundException($query->id);
        }

        return $category;
    }
}
