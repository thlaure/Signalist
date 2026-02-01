<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\Feed;

final readonly class GetFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
    ) {
    }

    public function __invoke(GetFeedQuery $query): Feed
    {
        $feed = $this->feedRepository->find($query->id);

        if (!$feed instanceof Feed) {
            throw new FeedNotFoundException($query->id);
        }

        return $feed;
    }
}
