<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Handler\GetBookmarkHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Entity\Bookmark;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private GetBookmarkHandler $handler;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new GetBookmarkHandler($this->bookmarkRepository);
    }

    public function testInvokeExistingBookmarkReturnsBookmark(): void
    {
        $bookmarkId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $bookmark = $this->createMock(Bookmark::class);

        $this->bookmarkRepository
            ->method('find')
            ->with($bookmarkId)
            ->willReturn($bookmark);

        $result = ($this->handler)(new GetBookmarkQuery($bookmarkId));

        $this->assertSame($bookmark, $result);
    }

    public function testInvokeNonExistentBookmarkThrowsBookmarkNotFoundException(): void
    {
        $this->bookmarkRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(BookmarkNotFoundException::class);

        ($this->handler)(new GetBookmarkQuery('non-existent-id'));
    }
}
