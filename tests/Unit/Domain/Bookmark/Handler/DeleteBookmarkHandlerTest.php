<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Bookmark\Command\DeleteBookmarkCommand;
use App\Domain\Bookmark\Exception\BookmarkNotFoundException;
use App\Domain\Bookmark\Handler\DeleteBookmarkHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private DeleteBookmarkHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->handler = new DeleteBookmarkHandler($this->bookmarkRepository);
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

    public function testInvokeWithExistingBookmarkDeletesIt(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();
        $bookmark = $this->createBookmarkWithOwner($this->ownerId);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('find')
            ->with($bookmarkId)
            ->willReturn($bookmark);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('delete')
            ->with($bookmark);

        $command = new DeleteBookmarkCommand($bookmarkId, $this->ownerId);

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

        $command = new DeleteBookmarkCommand($bookmarkId, $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithBookmarkOwnedByDifferentUserThrowsException(): void
    {
        $bookmarkId = Uuid::v7()->toRfc4122();
        $bookmark = $this->createBookmarkWithOwner(Uuid::v7()->toRfc4122());

        $this->bookmarkRepository->method('find')->willReturn($bookmark);

        $this->expectException(BookmarkNotFoundException::class);

        ($this->handler)(new DeleteBookmarkCommand($bookmarkId, $this->ownerId));
    }
}
