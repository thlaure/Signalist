<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(CreateCategoryCommand $command): string
    {
        if ($this->categoryRepository->findBySlug($command->slug) !== null) {
            throw new CategorySlugAlreadyExistsException($command->slug);
        }

        $category = new Category();
        $category->setName($command->name);
        $category->setSlug($command->slug);
        $category->setDescription($command->description);
        $category->setColor($command->color);
        $category->setPosition($command->position);

        $this->categoryRepository->save($category);

        return $category->getId()->toRfc4122();
    }
}
