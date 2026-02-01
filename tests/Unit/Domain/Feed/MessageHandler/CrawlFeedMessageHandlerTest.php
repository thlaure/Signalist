<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Feed\MessageHandler;

use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\MessageHandler\CrawlFeedMessageHandler;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Port\RssFetcherInterface;
use App\Entity\Feed;
use App\Infrastructure\RSS\RssFetchedArticle;
use App\Infrastructure\RSS\RssFetchResult;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Uid\Uuid;

final class CrawlFeedMessageHandlerTest extends TestCase
{
    private FeedRepositoryInterface&MockObject $feedRepository;

    private RssFetcherInterface&MockObject $rssFetcher;

    private EntityManagerInterface&MockObject $entityManager;

    private LoggerInterface&MockObject $logger;

    private CrawlFeedMessageHandler $handler;

    protected function setUp(): void
    {
        $this->feedRepository = $this->createMock(FeedRepositoryInterface::class);
        $this->rssFetcher = $this->createMock(RssFetcherInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new CrawlFeedMessageHandler(
            $this->feedRepository,
            $this->rssFetcher,
            $this->entityManager,
            $this->logger,
        );
    }

    public function testInvokeWithValidFeedFetchesAndSavesArticles(): void
    {
        $feedId = Uuid::v7();
        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn($feedId);
        $feed->method('getUrl')->willReturn('https://example.com/feed.xml');
        $feed->method('getTitle')->willReturn('My Feed');

        $this->feedRepository
            ->expects($this->once())
            ->method('find')
            ->with($feedId->toRfc4122())
            ->willReturn($feed);

        $fetchResult = new RssFetchResult(
            feedTitle: 'Example Feed',
            articles: [
                new RssFetchedArticle(
                    guid: 'article-1',
                    title: 'Article 1',
                    url: 'https://example.com/article-1',
                    summary: 'Summary 1',
                    publishedAt: new DateTimeImmutable(),
                ),
            ],
        );

        $this->rssFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with('https://example.com/feed.xml')
            ->willReturn($fetchResult);

        $articleRepository = $this->createMock(EntityRepository::class);
        $articleRepository
            ->method('findOneBy')
            ->willReturn(null); // Article doesn't exist

        $this->entityManager
            ->method('getRepository')
            ->willReturn($articleRepository);

        $this->entityManager
            ->expects($this->once())
            ->method('persist');

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $feed->expects($this->once())->method('setStatus')->with(Feed::STATUS_ACTIVE);
        $feed->expects($this->once())->method('setLastError')->with(null);
        $feed->expects($this->once())->method('setLastFetchedAt');
        $feed->expects($this->once())->method('setUpdatedAt');

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with('Feed crawled successfully', $this->anything());

        $message = new CrawlFeedMessage($feedId->toRfc4122());

        ($this->handler)($message);
    }

    public function testInvokeWithNonExistingFeedLogsWarningAndReturns(): void
    {
        $feedId = Uuid::v7()->toRfc4122();

        $this->feedRepository
            ->expects($this->once())
            ->method('find')
            ->with($feedId)
            ->willReturn(null);

        $this->logger
            ->expects($this->once())
            ->method('warning')
            ->with('Feed not found for crawling', ['feedId' => $feedId]);

        $this->rssFetcher
            ->expects($this->never())
            ->method('fetch');

        $message = new CrawlFeedMessage($feedId);

        ($this->handler)($message);
    }

    public function testInvokeWithFetchErrorSetsErrorStatusAndLogsError(): void
    {
        $feedId = Uuid::v7();
        $feed = $this->createMock(Feed::class);
        $feed->method('getId')->willReturn($feedId);
        $feed->method('getUrl')->willReturn('https://example.com/feed.xml');

        $this->feedRepository
            ->expects($this->once())
            ->method('find')
            ->with($feedId->toRfc4122())
            ->willReturn($feed);

        $this->rssFetcher
            ->expects($this->once())
            ->method('fetch')
            ->willThrowException(new RuntimeException('Network error'));

        $feed->expects($this->once())->method('setStatus')->with(Feed::STATUS_ERROR);
        $feed->expects($this->once())->method('setLastError')->with('Network error');
        $feed->expects($this->once())->method('setUpdatedAt');

        $this->feedRepository
            ->expects($this->once())
            ->method('save')
            ->with($feed);

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to crawl feed', $this->anything());

        $message = new CrawlFeedMessage($feedId->toRfc4122());

        ($this->handler)($message);
    }
}
