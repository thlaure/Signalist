<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Command\MarkArticleReadCommand;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Handler\MarkArticleReadHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Entity\Article;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class MarkArticleReadHandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;

    private MarkArticleReadHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new MarkArticleReadHandler($this->articleRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
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

    public function testInvokeWithExistingArticleMarksAsRead(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner($this->ownerId);

        $this->articleRepository
            ->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $article
            ->expects($this->once())
            ->method('setIsRead')
            ->with(true);

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with($article);

        $command = new MarkArticleReadCommand($articleId, true, $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithExistingArticleMarksAsUnread(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner($this->ownerId);

        $this->articleRepository
            ->expects($this->once())
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $article
            ->expects($this->once())
            ->method('setIsRead')
            ->with(false);

        $this->articleRepository
            ->expects($this->once())
            ->method('save')
            ->with($article);

        $command = new MarkArticleReadCommand($articleId, false, $this->ownerId);

        ($this->handler)($command);
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

        $command = new MarkArticleReadCommand($articleId, true, $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithArticleOwnedByDifferentUserThrowsException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createArticleWithOwner(Uuid::v7()->toRfc4122());

        $this->articleRepository->method('find')->willReturn($article);

        $this->expectException(ArticleNotFoundException::class);

        ($this->handler)(new MarkArticleReadCommand($articleId, true, $this->ownerId));
    }
}
