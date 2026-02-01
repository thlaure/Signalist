<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Exception\FeedUrlAlreadyExistsException;
use App\Domain\Feed\Handler\AddFeedHandler;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final class AddFeedHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private CategoryRepositoryInterface&MockObject $categoryRepository;

    private MessageBusInterface&MockObject $messageBus;

    private AddFeedHandler $handler;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepositoryInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);

        $this->handler = new AddFeedHandler(
            $this->feedRepository,
            $this->categoryRepository,
            $this->messageBus,
        );
    }

    public function testInvokeWithValidDataCreatesFeedAndDispatchesCrawlMessage(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $url = 'https://example.com/feed.xml';

        $category = $this->createMock(Category::class);

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrl')
            ->with($url)
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
            title: 'My Feed',
        );

        $result = ($this->handler)($command);

        $this->assertTrue(Uuid::isValid($result));
    }

    public function testInvokeWithDuplicateUrlThrowsFeedUrlAlreadyExistsException(): void
    {
        $url = 'https://example.com/feed.xml';
        $existingFeed = $this->createMock(Feed::class);

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrl')
            ->with($url)
            ->willReturn($existingFeed);

        $this->expectException(FeedUrlAlreadyExistsException::class);

        $command = new AddFeedCommand(
            url: $url,
            categoryId: Uuid::v7()->toRfc4122(),
        );

        ($this->handler)($command);
    }

    public function testInvokeWithInvalidCategoryThrowsCategoryNotFoundException(): void
    {
        $categoryId = Uuid::v7()->toRfc4122();
        $url = 'https://example.com/feed.xml';

        $this->feedRepository
            ->expects($this->once())
            ->method('findByUrl')
            ->with($url)
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
        );

        ($this->handler)($command);
    }
}
