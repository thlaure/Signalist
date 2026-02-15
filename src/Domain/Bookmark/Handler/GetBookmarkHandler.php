<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Entity\Bookmark;

final readonly class GetBookmarkHandler
{
    public function __construct(
        private BookmarkRepositoryInterface $bookmarkRepository,
    ) {
    }

    public function __invoke(GetBookmarkQuery $query): Bookmark
    {
        $bookmark = $this->bookmarkRepository->find($query->id);

        if (!$bookmark instanceof Bookmark) {
            throw new BookmarkNotFoundException($query->id);
        }

        if ($bookmark->getArticle()->getFeed()->getOwner()->getId()->toRfc4122() !== $query->ownerId) {
            throw new BookmarkNotFoundException($query->id);
        }

        return $bookmark;
    }
}
