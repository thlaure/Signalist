<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Exception\FeedUrlAlreadyExistsException;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AddFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(AddFeedCommand $command): string
    {
        if ($this->feedRepository->findByUrl($command->url) instanceof Feed) {
            throw new FeedUrlAlreadyExistsException($command->url);
        }

        $category = $this->categoryRepository->find($command->categoryId);

        if (!$category instanceof Category) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        $feed = new Feed();
        $feed->setUrl($command->url);
        $feed->setTitle($command->title ?? $command->url);
        $feed->setCategory($category);

        $this->feedRepository->save($feed);

        // Dispatch async crawl job to fetch articles
        $this->messageBus->dispatch(new CrawlFeedMessage($feed->getId()->toRfc4122()));

        return $feed->getId()->toRfc4122();
    }
}
