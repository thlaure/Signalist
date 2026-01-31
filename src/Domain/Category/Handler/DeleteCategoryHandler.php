<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Exception\CategoryHasFeedsException;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;

final readonly class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $category = $this->categoryRepository->find($command->id);

        if ($category === null) {
            throw new CategoryNotFoundException($command->id);
        }

        if ($this->categoryRepository->hasFeedsAssigned($command->id)) {
            throw new CategoryHasFeedsException($command->id);
        }

        $this->categoryRepository->delete($category);
    }
}
