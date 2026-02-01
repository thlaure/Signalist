<?php

declare(strict_types=1);

namespace App\Domain\Feed\MessageHandler;

use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Domain\Feed\Port\RssFetcherInterface;
use App\Entity\Article;
use App\Entity\Feed;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CrawlFeedMessageHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
        private RssFetcherInterface $rssFetcher,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(CrawlFeedMessage $message): void
    {
        $feed = $this->feedRepository->find($message->feedId);

        if (!$feed instanceof Feed) {
            $this->logger->warning('Feed not found for crawling', ['feedId' => $message->feedId]);

            return;
        }

        try {
            $result = $this->rssFetcher->fetch($feed->getUrl());

            // Update feed title if it was auto-generated from URL
            if ($feed->getTitle() === $feed->getUrl()) {
                $feed->setTitle($result->feedTitle);
            }

            $newArticlesCount = 0;
            foreach ($result->articles as $fetchedArticle) {
                // Check if article already exists (by guid)
                if ($this->articleExists($feed, $fetchedArticle->guid)) {
                    continue;
                }

                $article = new Article();
                $article->setGuid($fetchedArticle->guid);
                $article->setTitle($fetchedArticle->title);
                $article->setUrl($fetchedArticle->url);
                $article->setSummary($fetchedArticle->summary);
                $article->setContent($fetchedArticle->content);
                $article->setAuthor($fetchedArticle->author);
                $article->setImageUrl($fetchedArticle->imageUrl);
                $article->setPublishedAt($fetchedArticle->publishedAt);
                $article->setFeed($feed);

                $this->entityManager->persist($article);
                ++$newArticlesCount;
            }

            $feed->setStatus(Feed::STATUS_ACTIVE);
            $feed->setLastError(null);
            $feed->setLastFetchedAt(new DateTimeImmutable());
            $feed->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->flush();

            $this->logger->info('Feed crawled successfully', [
                'feedId' => $feed->getId()->toRfc4122(),
                'newArticles' => $newArticlesCount,
            ]);
        } catch (Exception $e) {
            $feed->setStatus(Feed::STATUS_ERROR);
            $feed->setLastError($e->getMessage());
            $feed->setUpdatedAt(new DateTimeImmutable());

            $this->feedRepository->save($feed);

            $this->logger->error('Failed to crawl feed', [
                'feedId' => $feed->getId()->toRfc4122(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function articleExists(Feed $feed, string $guid): bool
    {
        $existingArticle = $this->entityManager
            ->getRepository(Article::class)
            ->findOneBy([
                'feed' => $feed,
                'guid' => $guid,
            ]);

        return $existingArticle !== null;
    }
}
