<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Exception\CategoryHasFeedsException;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;

final readonly class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(DeleteCategoryCommand $command): void
    {
        $category = $this->categoryRepository->find($command->id);

        if (!$category instanceof Category) {
            throw new CategoryNotFoundException($command->id);
        }

        if ($category->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new CategoryNotFoundException($command->id);
        }

        if ($this->categoryRepository->hasFeedsAssigned($command->id)) {
            throw new CategoryHasFeedsException($command->id);
        }

        $this->categoryRepository->delete($category);
    }
}
