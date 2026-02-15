<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Article\DTO\Output;

use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ArticleOutputTest extends TestCase
{
    public function testFromEntityMapsAllFields(): void
    {
        $categoryId = Uuid::v7();
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn($categoryId);
        $category->method('getName')->willReturn('Tech');

        $feedId = Uuid::v7();
        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn($feedId);
        $feed->method('getTitle')->willReturn('Example Feed');
        $feed->method('getCategory')->willReturn($category);

        $articleId = Uuid::v7();
        $publishedAt = new DateTimeImmutable('2025-01-15T08:00:00+00:00');
        $createdAt = new DateTimeImmutable('2025-01-15T09:00:00+00:00');

        $article = $this->createMock(Article::class);
        $article->method('getId')->willReturn($articleId);
        $article->method('getTitle')->willReturn('Article Title');
        $article->method('getUrl')->willReturn('https://example.com/article');
        $article->method('getSummary')->willReturn('A summary');
        $article->method('getContent')->willReturn('<p>Full content</p>');
        $article->method('getAuthor')->willReturn('John Doe');
        $article->method('getImageUrl')->willReturn('https://example.com/image.jpg');
        $article->method('isRead')->willReturn(false);
        $article->method('getPublishedAt')->willReturn($publishedAt);
        $article->method('getCreatedAt')->willReturn($createdAt);
        $article->method('getFeed')->willReturn($feed);

        $output = ArticleOutput::fromEntity($article);

        $this->assertSame($articleId->toRfc4122(), $output->id);
        $this->assertSame('Article Title', $output->title);
        $this->assertSame('https://example.com/article', $output->url);
        $this->assertSame('A summary', $output->summary);
        $this->assertSame('<p>Full content</p>', $output->content);
        $this->assertSame('John Doe', $output->author);
        $this->assertSame('https://example.com/image.jpg', $output->imageUrl);
        $this->assertFalse($output->isRead);
        $this->assertSame('2025-01-15T08:00:00+00:00', $output->publishedAt);
        $this->assertSame('2025-01-15T09:00:00+00:00', $output->createdAt);
        $this->assertSame($feedId->toRfc4122(), $output->feedId);
        $this->assertSame('Example Feed', $output->feedTitle);
        $this->assertSame($categoryId->toRfc4122(), $output->categoryId);
        $this->assertSame('Tech', $output->categoryName);
    }

    public function testFromEntityWithNullableFields(): void
    {
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn(Uuid::v7());
        $category->method('getName')->willReturn('Tech');

        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn(Uuid::v7());
        $feed->method('getTitle')->willReturn('Feed');
        $feed->method('getCategory')->willReturn($category);

        $article = $this->createMock(Article::class);
        $article->method('getId')->willReturn(Uuid::v7());
        $article->method('getTitle')->willReturn('Title');
        $article->method('getUrl')->willReturn('https://example.com');
        $article->method('getSummary')->willReturn(null);
        $article->method('getContent')->willReturn(null);
        $article->method('getAuthor')->willReturn(null);
        $article->method('getImageUrl')->willReturn(null);
        $article->method('isRead')->willReturn(true);
        $article->method('getPublishedAt')->willReturn(null);
        $article->method('getCreatedAt')->willReturn(new DateTimeImmutable());
        $article->method('getFeed')->willReturn($feed);

        $output = ArticleOutput::fromEntity($article);

        $this->assertNull($output->summary);
        $this->assertNull($output->content);
        $this->assertNull($output->author);
        $this->assertNull($output->imageUrl);
        $this->assertNull($output->publishedAt);
        $this->assertTrue($output->isRead);
    }
}
