<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Category\DTO\Output\CategoryOutput;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Handler\ListCategoriesHandler;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Domain\Category\Query\ListCategoriesQuery;
use App\Entity\Category;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\CategoryResource;

use function assert;
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

            return array_map(self::toResource(...), $categories);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $category = ($this->getCategoryHandler)(new GetCategoryQuery($id, $ownerId));

        return self::toResource($category);
    }

    public static function toResource(Category $category): CategoryResource
    {
        $output = CategoryOutput::fromEntity($category);

        return new CategoryResource(
            id: $output->id,
            name: $output->name,
            slug: $output->slug,
            description: $output->description,
            color: $output->color,
            position: $output->position,
            createdAt: $output->createdAt,
            updatedAt: $output->updatedAt,
        );
    }
}
