<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\DTO\Output;

use App\Domain\Feed\DTO\Output\FeedOutput;
use App\Entity\Category;
use App\Entity\Feed;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class FeedOutputTest extends TestCase
{
    public function testFromEntityMapsAllFields(): void
    {
        $categoryId = Uuid::v7();
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn($categoryId);
        $category->method('getName')->willReturn('Tech');

        $feedId = Uuid::v7();
        $createdAt = new DateTimeImmutable('2025-01-01T10:00:00+00:00');
        $updatedAt = new DateTimeImmutable('2025-01-02T10:00:00+00:00');
        $lastFetchedAt = new DateTimeImmutable('2025-01-02T09:00:00+00:00');

        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn($feedId);
        $feed->method('getTitle')->willReturn('Example Feed');
        $feed->method('getUrl')->willReturn('https://example.com/feed');
        $feed->method('getStatus')->willReturn('active');
        $feed->method('getLastError')->willReturn(null);
        $feed->method('getLastFetchedAt')->willReturn($lastFetchedAt);
        $feed->method('getCategory')->willReturn($category);
        $feed->method('getCreatedAt')->willReturn($createdAt);
        $feed->method('getUpdatedAt')->willReturn($updatedAt);

        $output = FeedOutput::fromEntity($feed);

        $this->assertSame($feedId->toRfc4122(), $output->id);
        $this->assertSame('Example Feed', $output->title);
        $this->assertSame('https://example.com/feed', $output->url);
        $this->assertSame('active', $output->status);
        $this->assertNull($output->lastError);
        $this->assertSame('2025-01-02T09:00:00+00:00', $output->lastFetchedAt);
        $this->assertSame($categoryId->toRfc4122(), $output->categoryId);
        $this->assertSame('Tech', $output->categoryName);
        $this->assertSame('2025-01-01T10:00:00+00:00', $output->createdAt);
        $this->assertSame('2025-01-02T10:00:00+00:00', $output->updatedAt);
    }

    public function testFromEntityWithNullLastFetchedAt(): void
    {
        $category = $this->createMock(Category::class);
        $category->method('getId')->willReturn(Uuid::v7());
        $category->method('getName')->willReturn('Tech');

        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn(Uuid::v7());
        $feed->method('getTitle')->willReturn('Feed');
        $feed->method('getUrl')->willReturn('https://example.com/feed');
        $feed->method('getStatus')->willReturn('active');
        $feed->method('getLastError')->willReturn('Connection timeout');
        $feed->method('getLastFetchedAt')->willReturn(null);
        $feed->method('getCategory')->willReturn($category);
        $feed->method('getCreatedAt')->willReturn(new DateTimeImmutable());
        $feed->method('getUpdatedAt')->willReturn(new DateTimeImmutable());

        $output = FeedOutput::fromEntity($feed);

        $this->assertNull($output->lastFetchedAt);
        $this->assertSame('Connection timeout', $output->lastError);
    }
}
