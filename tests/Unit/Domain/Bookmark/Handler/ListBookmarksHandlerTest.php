<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Handler\ListBookmarksHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Bookmark;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListBookmarksHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private ListBookmarksHandler $handler;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new ListBookmarksHandler($this->bookmarkRepository);
    }

    public function testInvokeReturnsAllBookmarks(): void
    {
        $bookmarks = [
            $this->createMock(Bookmark::class),
            $this->createMock(Bookmark::class),
        ];

        $this->bookmarkRepository->method('findAll')->willReturn($bookmarks);

        $result = ($this->handler)();

        $this->assertCount(2, $result);
        $this->assertSame($bookmarks, $result);
    }

    public function testInvokeNoBookmarksExistReturnsEmptyArray(): void
    {
        $this->bookmarkRepository->method('findAll')->willReturn([]);

        $result = ($this->handler)();

        $this->assertSame([], $result);
    }
}
