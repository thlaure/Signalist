<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
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
    public function __invoke(): array
    {
        return $this->bookmarkRepository->findAll();
    }
}
