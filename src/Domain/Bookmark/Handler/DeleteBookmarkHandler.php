<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Command\DeleteBookmarkCommand;
use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Bookmark;

final readonly class DeleteBookmarkHandler
{
    public function __construct(
        private BookmarkRepositoryInterface $bookmarkRepository,
    ) {
    }

    public function __invoke(DeleteBookmarkCommand $command): void
    {
        $bookmark = $this->bookmarkRepository->find($command->id);

        if (!$bookmark instanceof Bookmark) {
            throw new BookmarkNotFoundException($command->id);
        }

        $this->bookmarkRepository->delete($bookmark);
    }
}
