<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Handler\ListArticlesHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\ListArticlesQuery;
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
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId])
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId);

        $result = ($this->handler)($query);

        $this->assertCount(2, $result);
    }

    public function testInvokeWithFeedIdFilterReturnsFilteredArticles(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'feedId' => $feedId])
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, feedId: $feedId);

        $result = ($this->handler)($query);

        $this->assertCount(1, $result);
    }

    public function testInvokeWithIsReadFilterReturnsFilteredArticles(): void
    {
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'isRead' => false])
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, isRead: false);

        $result = ($this->handler)($query);

        $this->assertCount(1, $result);
    }

    public function testInvokeWithMultipleFiltersAppliesAllFilters(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $categoryId = Uuid::v7()->toRfc4122();

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with([
                'ownerId' => $this->ownerId,
                'feedId' => $feedId,
                'categoryId' => $categoryId,
                'isRead' => true,
            ])
            ->willReturn([]);

        $query = new ListArticlesQuery(
            ownerId: $this->ownerId,
            feedId: $feedId,
            categoryId: $categoryId,
            isRead: true,
        );

        $result = ($this->handler)($query);

        $this->assertCount(0, $result);
    }

    public function testInvokeWithSearchFilterPassesSearchToRepository(): void
    {
        $articles = [$this->createMock(Article::class)];

        $this->articleRepository
            ->expects($this->once())
            ->method('findAll')
            ->with(['ownerId' => $this->ownerId, 'search' => 'css grid'])
            ->willReturn($articles);

        $query = new ListArticlesQuery(ownerId: $this->ownerId, search: 'css grid');

        $result = ($this->handler)($query);

        $this->assertCount(1, $result);
    }
}
