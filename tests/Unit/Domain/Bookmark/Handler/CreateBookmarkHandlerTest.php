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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class CreateBookmarkHandlerTest extends TestCase
{
    private BookmarkRepositoryInterface&MockObject $bookmarkRepository;

    private ArticleRepositoryInterface&MockObject $articleRepository;

    private CreateBookmarkHandler $handler;

    protected function setUp(): void
    {
        $this->bookmarkRepository = $this->createMock(BookmarkRepositoryInterface::class);
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);

        $this->handler = new CreateBookmarkHandler(
            $this->bookmarkRepository,
            $this->articleRepository,
        );
    }

    public function testInvokeWithValidArticleCreatesBookmark(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);

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

        $command = new CreateBookmarkCommand(articleId: $articleId);

        ($this->handler)($command);
    }

    public function testInvokeWithAlreadyBookmarkedArticleThrowsException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);
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

        $command = new CreateBookmarkCommand(articleId: $articleId);

        ($this->handler)($command);
    }
}
