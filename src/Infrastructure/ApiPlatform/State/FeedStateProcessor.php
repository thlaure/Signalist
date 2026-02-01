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
use App\Infrastructure\ApiPlatform\Resource\FeedResource;

use function assert;

use DateTimeInterface;

use function is_string;

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
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?FeedResource
    {
        if ($operation instanceof Post && $data instanceof AddFeedInput) {
            $id = ($this->addFeedHandler)(new AddFeedCommand(
                url: $data->url,
                categoryId: $data->categoryId,
                title: $data->title,
            ));

            $feed = ($this->getFeedHandler)(new GetFeedQuery($id));

            return new FeedResource(
                id: $feed->getId()->toRfc4122(),
                title: $feed->getTitle(),
                url: $feed->getUrl(),
                status: $feed->getStatus(),
                lastError: $feed->getLastError(),
                lastFetchedAt: $feed->getLastFetchedAt()?->format(DateTimeInterface::ATOM),
                categoryId: $feed->getCategory()->getId()->toRfc4122(),
                categoryName: $feed->getCategory()->getName(),
                createdAt: $feed->getCreatedAt()->format(DateTimeInterface::ATOM),
                updatedAt: $feed->getUpdatedAt()->format(DateTimeInterface::ATOM),
            );
        }

        if ($operation instanceof Put && $data instanceof UpdateFeedInput) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->updateFeedHandler)(new UpdateFeedCommand(
                id: $id,
                title: $data->title,
                categoryId: $data->categoryId,
                status: $data->status,
            ));

            $feed = ($this->getFeedHandler)(new GetFeedQuery($id));

            return new FeedResource(
                id: $feed->getId()->toRfc4122(),
                title: $feed->getTitle(),
                url: $feed->getUrl(),
                status: $feed->getStatus(),
                lastError: $feed->getLastError(),
                lastFetchedAt: $feed->getLastFetchedAt()?->format(DateTimeInterface::ATOM),
                categoryId: $feed->getCategory()->getId()->toRfc4122(),
                categoryName: $feed->getCategory()->getName(),
                createdAt: $feed->getCreatedAt()->format(DateTimeInterface::ATOM),
                updatedAt: $feed->getUpdatedAt()->format(DateTimeInterface::ATOM),
            );
        }

        if ($operation instanceof Delete) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->deleteFeedHandler)(new DeleteFeedCommand($id));

            return null;
        }

        return null;
    }
}
