<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Feed\Handler\ListFeedsHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Query\ListFeedsQuery;
use App\Entity\Feed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class ListFeedsHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private ListFeedsHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->handler = new ListFeedsHandler($this->feedRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeReturnsAllFeeds(): void
    {
        $feeds = [
            $this->createMock(Feed::class),
            $this->createMock(Feed::class),
        ];

        $this->feedRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn($feeds);

        $result = ($this->handler)(new ListFeedsQuery($this->ownerId));

        $this->assertCount(2, $result);
        $this->assertSame($feeds, $result);
    }

    public function testInvokeNoFeedsExistReturnsEmptyArray(): void
    {
        $this->feedRepository
            ->method('findAllByOwner')
            ->with($this->ownerId)
            ->willReturn([]);

        $result = ($this->handler)(new ListFeedsQuery($this->ownerId));

        $this->assertSame([], $result);
    }
}
