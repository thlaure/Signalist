<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\Handler;

use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\GetArticleQuery;
use App\Entity\Article;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetArticleHandlerTest extends TestCase
{
    private ArticleRepositoryInterface&MockObject $articleRepository;

    private GetArticleHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->articleRepository = $this->createMock(ArticleRepositoryInterface::class);
        $this->handler = new GetArticleHandler($this->articleRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeExistingArticleReturnsArticle(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $feed = $this->createMock(Feed::class);
        $feed->method('getOwner')->willReturn($owner);
        $article->method('getFeed')->willReturn($feed);

        $this->articleRepository
            ->method('find')
            ->with($articleId)
            ->willReturn($article);

        $result = ($this->handler)(new GetArticleQuery($articleId, $this->ownerId));

        $this->assertSame($article, $result);
    }

    public function testInvokeNonExistentArticleThrowsArticleNotFoundException(): void
    {
        $this->articleRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(ArticleNotFoundException::class);

        ($this->handler)(new GetArticleQuery('non-existent-id', $this->ownerId));
    }

    public function testInvokeArticleOwnedByDifferentUserThrowsArticleNotFoundException(): void
    {
        $articleId = Uuid::v7()->toRfc4122();
        $article = $this->createMock(Article::class);

        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn(Uuid::v7());
        $feed = $this->createMock(Feed::class);
        $feed->method('getOwner')->willReturn($otherOwner);
        $article->method('getFeed')->willReturn($feed);

        $this->articleRepository->method('find')->willReturn($article);

        $this->expectException(ArticleNotFoundException::class);

        ($this->handler)(new GetArticleQuery($articleId, $this->ownerId));
    }
}
