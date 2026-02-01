<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Handler\ListFeedsHandler;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\Feed;
use App\Infrastructure\ApiPlatform\Resource\FeedResource;

use function assert;

use DateTimeInterface;

use function is_string;

/**
 * @implements ProviderInterface<FeedResource>
 */
final readonly class FeedStateProvider implements ProviderInterface
{
    public function __construct(
        private GetFeedHandler $getFeedHandler,
        private ListFeedsHandler $listFeedsHandler,
    ) {
    }

    /**
     * @return FeedResource|array<int, FeedResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): FeedResource|array
    {
        if ($operation instanceof CollectionOperationInterface) {
            $feeds = ($this->listFeedsHandler)();

            return array_map($this->toResource(...), $feeds);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $feed = ($this->getFeedHandler)(new GetFeedQuery($id));

        return $this->toResource($feed);
    }

    private function toResource(Feed $feed): FeedResource
    {
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
}
