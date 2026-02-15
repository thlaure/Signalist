<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Bookmark\Handler;

use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Bookmark\Command\CreateBookmarkCommand;
use App\Domain\Bookmark\Exception\ArticleAlreadyBookmarkedException;
use App\Domain\Bookmark\Handler\CreateBookmarkHandler;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private ArticleRepositoryInterface&MockObject $articleRepository;

    private CreateBookmarkHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->ownerId = Uuid::v7()->toRfc4122();

        $this->handler = new CreateBookmarkHandler(
            $this->bookmarkRepository,
            $this->articleRepository,
        );
    }

    private function createArticleWithOwner(string $ownerIdString): Article&MockObject
    {
        $article = $this->createMock(Article::class);
        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($ownerIdString));
        $feed = $this->createMock(Feed::class);
        $feed->method('getOwner')->willReturn($owner);
        $article->method('getFeed')->willReturn($feed);

        return $article;
    }

    public function testInvokeWithValidArticleCreatesBookmark(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner($this->ownerId);

        $this->articleRepository
            ->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('findByArticle')
            ->with($articleId)
            ->willReturn(null);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Bookmark::class));

        $command = new CreateBookmarkCommand(
            articleId: $articleId,
            ownerId: $this->ownerId,
            notes: 'My notes',
        );

        $result = ($this->handler)($command);

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeWithNonExistingArticleThrowsException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();

        $this->articleRepository
            ->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn(null);

        $this->expectException(ArticleNotFoundException::class);

        $command = new CreateBookmarkCommand(articleId: $articleId, ownerId: $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithArticleOwnedByDifferentUserThrowsException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner(Uuid::v7()->toRfc4122());

        $this->articleRepository->method('find')->willReturn($article);

        $this->expectException(ArticleNotFoundException::class);

        ($this->handler)(new CreateBookmarkCommand(articleId: $articleId, ownerId: $this->ownerId));
    }

    public function testInvokeWithAlreadyBookmarkedArticleThrowsException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner($this->ownerId);
        $existingBookmark = $this->createMock(Bookmark::class);

        $this->articleRepository
            ->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $this->bookmarkRepository
            ->expects($this->once())
            ->method('findByArticle')
            ->with($articleId)
            ->willReturn($existingBookmark);

        $this->expectException(ArticleAlreadyBookmarkedException::class);

        $command = new CreateBookmarkCommand(articleId: $articleId, ownerId: $this->ownerId);

        ($this->handler)($command);
    }
}
