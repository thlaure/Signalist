<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Handler\ListCategoriesHandler;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Entity\Category;
use App\Infrastructure\ApiPlatform\Resource\CategoryResource;

use function assert;

use DateTimeInterface;

use function is_string;

/**
 * @implements ProviderInterface<CategoryResource>
 */
final readonly class CategoryStateProvider implements ProviderInterface
{
    public function __construct(
        private GetCategoryHandler $getCategoryHandler,
        private ListCategoriesHandler $listCategoriesHandler,
    ) {
    }

    /**
     * @return CategoryResource|array<int, CategoryResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CategoryResource|array
    {
        if ($operation instanceof CollectionOperationInterface) {
            $categories = ($this->listCategoriesHandler)();

            return array_map($this->toResource(...), $categories);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $category = ($this->getCategoryHandler)(new GetCategoryQuery($id));

        return $this->toResource($category);
    }

    private function toResource(Category $category): CategoryResource
    {
        return new CategoryResource(
            id: $category->getId()->toRfc4122(),
            name: $category->getName(),
            slug: $category->getSlug(),
            description: $category->getDescription(),
            color: $category->getColor(),
            position: $category->getPosition(),
            createdAt: $category->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $category->getUpdatedAt()->format(DateTimeInterface::ATOM),
        );
    }
}
