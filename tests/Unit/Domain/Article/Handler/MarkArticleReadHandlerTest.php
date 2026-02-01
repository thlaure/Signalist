<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Command\MarkArticleReadCommand;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Handler\MarkArticleReadHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Entity\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class MarkArticleReadHandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;

    private MarkArticleReadHandler $handler;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new MarkArticleReadHandler($this->articleRepository);
    }

    public function testInvokeWithExistingArticleMarksAsRead(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);

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

        $command = new MarkArticleReadCommand($articleId, true);

        ($this->handler)($command);
    }

    public function testInvokeWithExistingArticleMarksAsUnread(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);

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

        $command = new MarkArticleReadCommand($articleId, false);

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

        $command = new MarkArticleReadCommand($articleId, true);

        ($this->handler)($command);
    }
}
