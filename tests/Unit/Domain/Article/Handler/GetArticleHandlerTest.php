<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\GetArticleQuery;
use App\Entity\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetArticleHandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;

    private GetArticleHandler $handler;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticleHandler($this->articleRepository);
    }

    public function testInvokeExistingArticleReturnsArticle(): void
    {
        $articleId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $article = $this->createMock(Article::class);

        $this->articleRepository
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $result = ($this->handler)(new GetArticleQuery($articleId));

        $this->assertSame($article, $result);
    }

    public function testInvokeNonExistentArticleThrowsArticleNotFoundException(): void
    {
        $this->articleRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(ArticleNotFoundException::class);

        ($this->handler)(new GetArticleQuery('non-existent-id'));
    }
}
