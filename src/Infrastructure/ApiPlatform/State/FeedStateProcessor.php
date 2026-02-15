<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Command\DeleteFeedCommand;
use App\Domain\Feed\Command\UpdateFeedCommand;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\DTO\Input\UpdateFeedInput;
use App\Domain\Feed\Handler\AddFeedHandler;
use App\Domain\Feed\Handler\DeleteFeedHandler;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Handler\UpdateFeedHandler;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\FeedResource;

use function assert;
use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<AddFeedInput|UpdateFeedInput, FeedResource|null>
 */
final readonly class FeedStateProcessor implements ProcessorInterface
{
    public function __construct(
        private AddFeedHandler $addFeedHandler,
        private UpdateFeedHandler $updateFeedHandler,
        private DeleteFeedHandler $deleteFeedHandler,
        private GetFeedHandler $getFeedHandler,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?FeedResource
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof Post && $data instanceof AddFeedInput) {
            $id = ($this->addFeedHandler)(new AddFeedCommand(
                url: $data->url,
                categoryId: $data->categoryId,
                ownerId: $ownerId,
                title: $data->title,
            ));

            $feed = ($this->getFeedHandler)(new GetFeedQuery($id, $ownerId));

            return FeedStateProvider::toResource($feed);
        }

        if ($operation instanceof Put && $data instanceof UpdateFeedInput) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->updateFeedHandler)(new UpdateFeedCommand(
                id: $id,
                title: $data->title,
                categoryId: $data->categoryId,
                status: $data->status,
                ownerId: $ownerId,
            ));

            $feed = ($this->getFeedHandler)(new GetFeedQuery($id, $ownerId));

            return FeedStateProvider::toResource($feed);
        }

        if ($operation instanceof Delete) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->deleteFeedHandler)(new DeleteFeedCommand($id, $ownerId));

            return null;
        }

        return null;
    }
}
