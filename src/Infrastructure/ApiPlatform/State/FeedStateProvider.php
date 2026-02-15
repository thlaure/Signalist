<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Feed\DTO\Output\FeedOutput;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Handler\ListFeedsHandler;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Domain\Feed\Query\ListFeedsQuery;
use App\Entity\Feed;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\FeedResource;

use function assert;
use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<FeedResource>
 */
final readonly class FeedStateProvider implements ProviderInterface
{
    public function __construct(
        private GetFeedHandler $getFeedHandler,
        private ListFeedsHandler $listFeedsHandler,
        private Security $security,
    ) {
    }

    /**
     * @return FeedResource|array<int, FeedResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): FeedResource|array
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof CollectionOperationInterface) {
            $feeds = ($this->listFeedsHandler)(new ListFeedsQuery($ownerId));

            return array_map(self::toResource(...), $feeds);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $feed = ($this->getFeedHandler)(new GetFeedQuery($id, $ownerId));

        return self::toResource($feed);
    }

    public static function toResource(Feed $feed): FeedResource
    {
        $output = FeedOutput::fromEntity($feed);

        return new FeedResource(
            id: $output->id,
            title: $output->title,
            url: $output->url,
            status: $output->status,
            lastError: $output->lastError,
            lastFetchedAt: $output->lastFetchedAt,
            categoryId: $output->categoryId,
            categoryName: $output->categoryName,
            createdAt: $output->createdAt,
            updatedAt: $output->updatedAt,
        );
    }
}
