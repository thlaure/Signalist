<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Handler\ListArticlesHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\ListArticlesQuery;
use App\Domain\Article\Query\PaginatedArticlesResult;
use App\Entity\Article;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListArticlesHandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;

    private ListArticlesHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new ListArticlesHandler($this->articleRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeWithNoFiltersReturnsAllArticles(): void
    {
        $articles = [
            $this->createMock(Article::class),
            $this->createMock(Article::class),
        ];

        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with(['ownerId' => $this->ownerId])
            ->willReturn(2);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId], 1, 20)
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId);

        $result = ($this->handler)($query);

        $this->assertInstanceOf(PaginatedArticlesResult::class, $result);
        $this->assertCount(2, $result->items);
        $this->assertSame(2, $result->total);
        $this->assertSame(1, $result->page);
        $this->assertSame(20, $result->limit);
        $this->assertSame(1, $result->pages);
    }

    public function testInvokeWithFeedIdFilterReturnsFilteredArticles(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with(['ownerId' => $this->ownerId, 'feedId' => $feedId])
            ->willReturn(1);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'feedId' => $feedId], 1, 20)
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, feedId: $feedId);

        $result = ($this->handler)($query);

        $this->assertCount(1, $result->items);
    }

    public function testInvokeWithIsReadFilterReturnsFilteredArticles(): void
    {
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with(['ownerId' => $this->ownerId, 'isRead' => false])
            ->willReturn(1);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'isRead' => false], 1, 20)
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, isRead: false);

        $result = ($this->handler)($query);

        $this->assertCount(1, $result->items);
    }

    public function testInvokeWithMultipleFiltersAppliesAllFilters(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $categoryId = Uuid::v7()->toRfc4122();

        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with([
                'ownerId' => $this->ownerId,
                'feedId' => $feedId,
                'categoryId' => $categoryId,
                'isRead' => true,
            ])
            ->willReturn(0);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with([
                'ownerId' => $this->ownerId,
                'feedId' => $feedId,
                'categoryId' => $categoryId,
                'isRead' => true,
            ], 1, 20)
            ->willReturn([]);

        $query = new ListArticlesQuery(
            ownerId: $this->ownerId,
            feedId: $feedId,
            categoryId: $categoryId,
            isRead: true,
        );

        $result = ($this->handler)($query);

        $this->assertCount(0, $result->items);
    }

    public function testInvokeWithSearchFilterPassesSearchToRepository(): void
    {
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with(['ownerId' => $this->ownerId, 'search' => 'css grid'])
            ->willReturn(1);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'search' => 'css grid'], 1, 20)
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, search: 'css grid');

        $result = ($this->handler)($query);

        $this->assertCount(1, $result->items);
    }

    public function testInvokeWithPageAndLimitPaginatesCorrectly(): void
    {
        $this->articleRepository
            ->expects($this->once())
            ->method('countAll')
            ->with(['ownerId' => $this->ownerId])
            ->willReturn(45);

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId], 2, 20)
            ->willReturn([]);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, page: 2, limit: 20);

        $result = ($this->handler)($query);

        $this->assertSame(45, $result->total);
        $this->assertSame(2, $result->page);
        $this->assertSame(20, $result->limit);
        $this->assertSame(3, $result->pages);
    }
}
