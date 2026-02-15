<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\Feed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private GetFeedHandler $handler;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->handler = new GetFeedHandler($this->feedRepository);
    }

    public function testInvokeExistingFeedReturnsFeed(): void
    {
        $feedId = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $feed = $this->createMock(Feed::class);

        $this->feedRepository
            ->method('find')
            ->with($feedId)
            ->willReturn($feed);

        $result = ($this->handler)(new GetFeedQuery($feedId));

        $this->assertSame($feed, $result);
    }

    public function testInvokeNonExistentFeedThrowsFeedNotFoundException(): void
    {
        $this->feedRepository
            ->method('find')
            ->willReturn(null);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new GetFeedQuery('non-existent-id'));
    }
}
