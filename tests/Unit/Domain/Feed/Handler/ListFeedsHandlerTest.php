<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Feed\Handler\ListFeedsHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Feed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListFeedsHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private ListFeedsHandler $handler;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->handler = new ListFeedsHandler($this->feedRepository);
    }

    public function testInvokeReturnsAllFeeds(): void
    {
        $feeds = [
            $this->createMock(Feed::class),
            $this->createMock(Feed::class),
        ];

        $this->feedRepository->method('findAll')->willReturn($feeds);

        $result = ($this->handler)();

        $this->assertCount(2, $result);
        $this->assertSame($feeds, $result);
    }

    public function testInvokeNoFeedsExistReturnsEmptyArray(): void
    {
        $this->feedRepository->method('findAll')->willReturn([]);

        $result = ($this->handler)();

        $this->assertSame([], $result);
    }
}
