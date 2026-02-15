<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Exception\FeedUrlAlreadyExistsException;
use App\Domain\Feed\Handler\AddFeedHandler;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class AddFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private UserRepositoryInterface&MockObject $userRepository;

    private MessageBusInterface&MockObject $messageBus;

    private AddFeedHandler $handler;

    private string $ownerId;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        $this->ownerId = Uuid::v7()->toRfc4122();

        $this->handler = new AddFeedHandler(
            $this->feedRepository,
            $this->categoryRepository,
            $this->userRepository,
            $this->messageBus,
        );
    }

    public function testInvokeWithValidDataCreatesFeedAndDispatchesCrawlMessage(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $url = 'https://example.com/feed.xml';

        $user = $this->createMock(User::class);
        $category = $this->createMock(Category::class);

        $ownerUuid = Uuid::fromString($this->ownerId);
        $categoryOwner = $this->createMock(User::class);
        $categoryOwner->method('getId')->willReturn($ownerUuid);
        $category->method('getOwner')->willReturn($categoryOwner);

        $this->userRepository
            ->expects($this->once())
            ->method('find')
            ->with($this->ownerId)
            ->willReturn($user);

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrlAndOwner')
            ->with($url, $this->ownerId)
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('find')
            ->with($categoryId)
            ->willReturn($category);

        $this->feedRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Feed::class));

        $this->messageBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CrawlFeedMessage::class))
            ->willReturn(new Envelope(new CrawlFeedMessage('test')));

        $command = new AddFeedCommand(
            url: $url,
            categoryId: $categoryId,
            ownerId: $this->ownerId,
            title: 'My Feed',
        );

        $result = ($this->handler)($command);

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeWithDuplicateUrlThrowsFeedUrlAlreadyExistsException(): void
    {
        $url = 'https://example.com/feed.xml';
        $existingFeed = $this->createMock(Feed::class);
        $user = $this->createMock(User::class);

        $this->userRepository->method('find')->willReturn($user);

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrlAndOwner')
            ->with($url, $this->ownerId)
            ->willReturn($existingFeed);

        $this->expectException(FeedUrlAlreadyExistsException::class);

        $command = new AddFeedCommand(
            url: $url,
            categoryId: Uuid::v7()->toRfc4122(),
            ownerId: $this->ownerId,
        );

        ($this->handler)($command);
    }

    public function testInvokeWithInvalidCategoryThrowsCategoryNotFoundException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $url = 'https://example.com/feed.xml';
        $user = $this->createMock(User::class);

        $this->userRepository->method('find')->willReturn($user);

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrlAndOwner')
            ->with($url, $this->ownerId)
            ->willReturn(null);

        $this->categoryRepository
            ->expects($this->once())
            ->method('find')
            ->with($categoryId)
            ->willReturn(null);

        $this->expectException(CategoryNotFoundException::class);

        $command = new AddFeedCommand(
            url: $url,
            categoryId: $categoryId,
            ownerId: $this->ownerId,
        );

        ($this->handler)($command);
    }

    public function testInvokeWithCategoryOwnedByDifferentUserThrowsCategoryNotFoundException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $url = 'https://example.com/feed.xml';
        $user = $this->createMock(User::class);
        $category = $this->createMock(Category::class);

        $otherOwnerId = Uuid::v7();
        $otherOwner = $this->createMock(User::class);
        $otherOwner->method('getId')->willReturn($otherOwnerId);
        $category->method('getOwner')->willReturn($otherOwner);

        $this->userRepository->method('find')->willReturn($user);
        $this->feedRepository->method('findByUrlAndOwner')->willReturn(null);
        $this->categoryRepository->method('find')->willReturn($category);

        $this->expectException(CategoryNotFoundException::class);

        $command = new AddFeedCommand(
            url: $url,
            categoryId: $categoryId,
            ownerId: $this->ownerId,
        );

        ($this->handler)($command);
    }
}
