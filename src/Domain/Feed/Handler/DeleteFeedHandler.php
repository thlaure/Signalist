<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Feed\Command\DeleteFeedCommand;
use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Feed;

final readonly class DeleteFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
    ) {
    }

    public function __invoke(DeleteFeedCommand $command): void
    {
        $feed = $this->feedRepository->find($command->id);

        if (!$feed instanceof Feed) {
            throw new FeedNotFoundException($command->id);
        }

        if ($feed->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new FeedNotFoundException($command->id);
        }

        $this->feedRepository->delete($feed);
    }
}
