<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use DateTimeImmutable;

final readonly class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(UpdateCategoryCommand $command): void
    {
        $category = $this->categoryRepository->find($command->id);

        if (!$category instanceof Category) {
            throw new CategoryNotFoundException($command->id);
        }

        if ($category->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new CategoryNotFoundException($command->id);
        }

        $existingWithSlug = $this->categoryRepository->findBySlugAndOwner($command->slug, $command->ownerId);

        if ($existingWithSlug instanceof Category && $existingWithSlug->getId()->toRfc4122() !== $command->id) {
            throw new CategorySlugAlreadyExistsException($command->slug);
        }

        $category->setName($command->name);
        $category->setSlug($command->slug);
        $category->setDescription($command->description);
        $category->setColor($command->color);
        $category->setPosition($command->position);
        $category->setUpdatedAt(new DateTimeImmutable());

        $this->categoryRepository->save($category);
    }
}
