<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Category\Command\CreateCategoryCommand;
use App\Domain\Category\Command\DeleteCategoryCommand;
use App\Domain\Category\Command\UpdateCategoryCommand;
use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\DTO\Input\UpdateCategoryInput;
use App\Domain\Category\Handler\CreateCategoryHandler;
use App\Domain\Category\Handler\DeleteCategoryHandler;
use App\Domain\Category\Handler\GetCategoryHandler;
use App\Domain\Category\Handler\UpdateCategoryHandler;
use App\Domain\Category\Query\GetCategoryQuery;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\CategoryResource;

use function assert;

use DateTimeInterface;

use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<CreateCategoryInput|UpdateCategoryInput, CategoryResource|null>
 */
final readonly class CategoryStateProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateCategoryHandler $createCategoryHandler,
        private UpdateCategoryHandler $updateCategoryHandler,
        private DeleteCategoryHandler $deleteCategoryHandler,
        private GetCategoryHandler $getCategoryHandler,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?CategoryResource
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof Post && $data instanceof CreateCategoryInput) {
            $id = ($this->createCategoryHandler)(new CreateCategoryCommand(
                name: $data->name,
                slug: $data->slug,
                ownerId: $ownerId,
                description: $data->description,
                color: $data->color,
                position: $data->position,
            ));

            $category = ($this->getCategoryHandler)(new GetCategoryQuery($id, $ownerId));

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

        if ($operation instanceof Put && $data instanceof UpdateCategoryInput) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->updateCategoryHandler)(new UpdateCategoryCommand(
                id: $id,
                name: $data->name,
                slug: $data->slug,
                ownerId: $ownerId,
                description: $data->description,
                color: $data->color,
                position: $data->position,
            ));

            $category = ($this->getCategoryHandler)(new GetCategoryQuery($id, $ownerId));

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

        if ($operation instanceof Delete) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->deleteCategoryHandler)(new DeleteCategoryCommand($id, $ownerId));

            return null;
        }

        return null;
    }
}
