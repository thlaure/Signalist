<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Feed\Command\DeleteFeedCommand;
use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Handler\DeleteFeedHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class DeleteFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private DeleteFeedHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->handler = new DeleteFeedHandler($this->feedRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeWithExistingFeedDeletesFeed(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $feed = $this->createMock(Feed::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $feed->method('getOwner')->willReturn($owner);

        $this->feedRepository
            ->expects($this->once())
            ->method('find')
            ->with($feedId)
            ->willReturn($feed);

        $this->feedRepository
            ->expects($this->once())
            ->method('delete')
            ->with($feed);

        $command = new DeleteFeedCommand($feedId, $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithNonExistingFeedThrowsFeedNotFoundException(): void
    {
        $feedId = Uuid::v7()->toRfc4122();

        $this->feedRepository
            ->expects($this->once())
            ->method('find')
            ->with($feedId)
            ->willReturn(null);

        $this->expectException(FeedNotFoundException::class);

        $command = new DeleteFeedCommand($feedId, $this->ownerId);

        ($this->handler)($command);
    }

    public function testInvokeWithFeedOwnedByDifferentUserThrowsFeedNotFoundException(): void
    {
        $feedId = Uuid::v7()->toRfc4122();
        $feed = $this->createMock(Feed::class);

        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn(Uuid::v7());
        $feed->method('getOwner')->willReturn($otherOwner);

        $this->feedRepository->method('find')->willReturn($feed);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new DeleteFeedCommand($feedId, $this->ownerId));
    }
}
