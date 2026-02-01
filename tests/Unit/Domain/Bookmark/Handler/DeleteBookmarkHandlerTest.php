<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Command\DeleteBookmarkCommand;
use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Handler\DeleteBookmarkHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Bookmark;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private DeleteBookmarkHandler $handler;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new DeleteBookmarkHandler($this->bookmarkRepository);
    }

    public function testInvokeWithExistingBookmarkDeletesIt(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();
        $bookmark = $this->createMock(Bookmark::class);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('find')
            ->with($bookmarkId)
            ->willReturn($bookmark);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('delete')
            ->with($bookmark);

        $command = new DeleteBookmarkCommand($bookmarkId);

        ($this->handler)($command);
    }

    public function testInvokeWithNonExistingBookmarkThrowsException(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('find')
            ->with($bookmarkId)
            ->willReturn(null);

        $this->expectException(BookmarkNotFoundException::class);

        $command = new DeleteBookmarkCommand($bookmarkId);

        ($this->handler)($command);
    }
}
