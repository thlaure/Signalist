<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\UpdateFeedCommand;
use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Handler\UpdateFeedHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UpdateFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private UpdateFeedHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->handler = new UpdateFeedHandler($this->feedRepository, $this->categoryRepository);
        $this->ownerId = Uuid::v7()->toRfc4122();
    }

    public function testInvokeValidUpdateSavesFeed(): void
    {
        $feed = $this->createMock(Feed::class);
        $category = $this->createMock(Category::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $feed->method('getOwner')->willReturn($owner);
        $category->method('getOwner')->willReturn($owner);

        $this->feedRepository->method('find')->with('feed-id')->willReturn($feed);
        $this->categoryRepository->method('find')->with('cat-id')->willReturn($category);

        $feed->expects($this->once())->method('setTitle')->with('New Title');
        $feed->expects($this->once())->method('setCategory')->with($category);
        $feed->expects($this->once())->method('setStatus')->with('active');
        $this->feedRepository->expects($this->once())->method('save');

        ($this->handler)(new UpdateFeedCommand(
            id: 'feed-id',
            title: 'New Title',
            categoryId: 'cat-id',
            status: 'active',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeNonExistentFeedThrowsFeedNotFoundException(): void
    {
        $this->feedRepository->method('find')->willReturn(null);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new UpdateFeedCommand(
            id: 'non-existent',
            title: 'Title',
            categoryId: 'cat-id',
            status: 'active',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeFeedOwnedByDifferentUserThrowsFeedNotFoundException(): void
    {
        $feed = $this->createMock(Feed::class);

        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn(Uuid::v7());
        $feed->method('getOwner')->willReturn($otherOwner);

        $this->feedRepository->method('find')->willReturn($feed);

        $this->expectException(FeedNotFoundException::class);

        ($this->handler)(new UpdateFeedCommand(
            id: 'feed-id',
            title: 'Title',
            categoryId: 'cat-id',
            status: 'active',
            ownerId: $this->ownerId,
        ));
    }

    public function testInvokeNonExistentCategoryThrowsCategoryNotFoundException(): void
    {
        $feed = $this->createMock(Feed::class);

        $owner = $this->createMock(User::class);
        $owner->method('getId')->willReturn(Uuid::fromString($this->ownerId));
        $feed->method('getOwner')->willReturn($owner);

        $this->feedRepository->method('find')->with('feed-id')->willReturn($feed);
        $this->categoryRepository->method('find')->with('bad-cat')->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        ($this->handler)(new UpdateFeedCommand(
            id: 'feed-id',
            title: 'Title',
            categoryId: 'bad-cat',
            status: 'active',
            ownerId: $this->ownerId,
        ));
    }
}
