<?php

declare(strict_types=1);

namespace App\Domain\Category\Handler;

use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Exception\CategorySlugAlreadyExistsException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Entity\Category;
use App\Entity\User;
use RuntimeException;

final readonly class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(CreateCategoryCommand $command): string
    {
        $user = $this->userRepository->find($command->ownerId);

        if (!$user instanceof User) {
            throw new RuntimeException('User not found');
        }

        if ($this->categoryRepository->findBySlugAndOwner($command->slug, $command->ownerId) instanceof Category) {
            throw new CategorySlugAlreadyExistsException($command->slug);
        }

        $category = new Category();
        $category->setName($command->name);
        $category->setSlug($command->slug);
        $category->setDescription($command->description);
        $category->setColor($command->color);
        $category->setPosition($command->position);
        $category->setOwner($user);

        $this->categoryRepository->save($category);

        return $category->getId()->toRfc4122();
    }
}
