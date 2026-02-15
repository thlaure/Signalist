<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class GetFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private GetFeedHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->handler = new GetFeedHandler($this->feedRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeExistingFeedReturnsFeed(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $feed = $this->createMock(Feed::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $feed->method('getOwner')->willReturn($owner);

        $this->feedRepository
            ->method('find')
            ->with($feedId)
            ->willReturn($feed);

        $result = ($this->handler)(new GetFeedQuery($feedId, $this->ownerId));

        $this->assertSame($feed, $result);
    }

    public function testInvokeNonExistentFeedThrowsFeedNotFoundException(): void
    {
        $this->feedRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new GetFeedQuery('non-existent-id', $this->ownerId));
    }

    public function testInvokeFeedOwnedByDifferentUserThrowsFeedNotFoundException(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $feed = $this->createMock(Feed::class);

        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn(Uuid::v7());
        $feed->method('getOwner')->willReturn($otherOwner);

        $this->feedRepository->method('find')->willReturn($feed);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new GetFeedQuery($feedId, $this->ownerId));
    }
}
