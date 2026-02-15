<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Handler\ListCategoriesHandler;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Domain\Category\Query\ListCategoriesQuery;
use App\Entity\Category;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\CategoryResource;

use function assert;

use DateTimeInterface;

use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<CategoryResource>
 */
final readonly class CategoryStateProvider implements ProviderInterface
{
    public function __construct(
        private GetCategoryHandler $getCategoryHandler,
        private ListCategoriesHandler $listCategoriesHandler,
        private Security $security,
    ) {
    }

    /**
     * @return CategoryResource|array<int, CategoryResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CategoryResource|array
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof CollectionOperationInterface) {
            $categories = ($this->listCategoriesHandler)(new ListCategoriesQuery($ownerId));

            return array_map($this->toResource(...), $categories);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $category = ($this->getCategoryHandler)(new GetCategoryQuery($id, $ownerId));

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
