<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Query\ListFeedsQuery;
use App\Entity\Feed;

final readonly class ListFeedsHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
    ) {
    }

    /**
     * @return Feed[]
     */
    public function __invoke(ListFeedsQuery $query): array
    {
        return $this->feedRepository->findAllByOwner($query->ownerId);
    }
}
