<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Domain\Bookmark\Query\ListBookmarksQuery;
use App\Entity\Bookmark;

final readonly class ListBookmarksHandler
{
    public function __construct(
        private BookmarkRepositoryInterface $bookmarkRepository,
    ) {
    }

    /**
     * @return Bookmark[]
     */
    public function __invoke(ListBookmarksQuery $query): array
    {
        return $this->bookmarkRepository->findAllByOwner($query->ownerId);
    }
}
