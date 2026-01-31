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
            provider: CategoryStateProvider::class,
        ),
        new Get(
            uriTemplate: '/categories/{id}',
            provider: CategoryStateProvider::class,
        ),
        new Post(
            uriTemplate: '/categories',
            input: CreateCategoryInput::class,
            processor: CategoryStateProcessor::class,
        ),
        new Put(
            uriTemplate: '/categories/{id}',
            input: UpdateCategoryInput::class,
            provider: CategoryStateProvider::class,
            processor: CategoryStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/categories/{id}',
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
