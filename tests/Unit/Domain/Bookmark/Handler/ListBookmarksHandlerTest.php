<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Handler\ListBookmarksHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Domain\Bookmark\Query\ListBookmarksQuery;
use App\Entity\Bookmark;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListBookmarksHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private ListBookmarksHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new ListBookmarksHandler($this->bookmarkRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeReturnsAllBookmarks(): void
    {
        $bookmarks = [
            $this->createMock(Bookmark::class),
            $this->createMock(Bookmark::class),
        ];

        $this->bookmarkRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn($bookmarks);

        $result = ($this->handler)(new ListBookmarksQuery($this->ownerId));

        $this->assertCount(2, $result);
        $this->assertSame($bookmarks, $result);
    }

    public function testInvokeNoBookmarksExistReturnsEmptyArray(): void
    {
        $this->bookmarkRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn([]);

        $result = ($this->handler)(new ListBookmarksQuery($this->ownerId));

        $this->assertSame([], $result);
    }
}
