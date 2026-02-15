<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Handler\GetBookmarkHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private GetBookmarkHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new GetBookmarkHandler($this->bookmarkRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    private function createBookmarkWithOwner(string $ownerIdString): Bookmark&MockObject
    {
        $bookmark = $this->createMock(Bookmark::class);
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($ownerIdString));
        $feed = $this->createMock(Feed::class);
        $feed->method('getOwner')->willReturn($owner);
        $article = $this->createMock(Article::class);
        $article->method('getFeed')->willReturn($feed);
        $bookmark->method('getArticle')->willReturn($article);

        return $bookmark;
    }

    public function testInvokeExistingBookmarkReturnsBookmark(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();
        $bookmark = $this->createBookmarkWithOwner($this->ownerId);

        $this->bookmarkRepository
            ->method('find')
            ->with($bookmarkId)
            ->willReturn($bookmark);

        $result = ($this->handler)(new GetBookmarkQuery($bookmarkId, $this->ownerId));

        $this->assertSame($bookmark, $result);
    }

    public function testInvokeNonExistentBookmarkThrowsBookmarkNotFoundException(): void
    {
        $this->bookmarkRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(BookmarkNotFoundException::class);

        ($this->handler)(new GetBookmarkQuery('non-existent-id', $this->ownerId));
    }

    public function testInvokeBookmarkOwnedByDifferentUserThrowsBookmarkNotFoundException(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();
        $bookmark = $this->createBookmarkWithOwner(Uuid::v7()->toRfc4122());

        $this->bookmarkRepository->method('find')->willReturn($bookmark);

        $this->expectException(BookmarkNotFoundException::class);

        ($this->handler)(new GetBookmarkQuery($bookmarkId, $this->ownerId));
    }
}
