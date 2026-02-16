<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\DTO\Input\UpdateCategoryInput;
use App\Infrastructure\ApiPlatform\State\CategoryStateProcessor;
use App\Infrastructure\ApiPlatform\State\CategoryStateProvider;

#[ApiResource(
    shortName: 'Category',
    operations: [
        new GetCollection(
            uriTemplate: '/categories',
            description: 'Retrieve all categories belonging to the authenticated user, ordered by position.',
            provider: CategoryStateProvider::class,
        ),
        new Get(
            uriTemplate: '/categories/{id}',
            description: 'Retrieve a single category by its UUID.',
            provider: CategoryStateProvider::class,
        ),
        new Post(
            uriTemplate: '/categories',
            description: 'Create a new category for organizing feeds. Slug must be unique per user.',
            input: CreateCategoryInput::class,
            processor: CategoryStateProcessor::class,
        ),
        new Put(
            uriTemplate: '/categories/{id}',
            description: 'Update an existing category. All fields are replaced.',
            input: UpdateCategoryInput::class,
            provider: CategoryStateProvider::class,
            processor: CategoryStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/categories/{id}',
            description: 'Delete a category. Fails if the category still has feeds assigned.',
            provider: CategoryStateProvider::class,
            processor: CategoryStateProcessor::class,
        ),
    ],
)]
final readonly class CategoryResource
{
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public ?string $description,
        public ?string $color,
        public int $position,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }
}
