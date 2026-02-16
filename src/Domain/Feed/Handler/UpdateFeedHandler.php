<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\UpdateFeedCommand;
use App\Domain\Feed\Exception\FeedNotFoundException;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use DateTimeImmutable;

final readonly class UpdateFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
        private CategoryRepositoryInterface $categoryRepository,
    ) {
    }

    public function __invoke(UpdateFeedCommand $command): void
    {
        $feed = $this->feedRepository->find($command->id);

        if (!$feed instanceof Feed) {
            throw new FeedNotFoundException($command->id);
        }

        if ($feed->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new FeedNotFoundException($command->id);
        }

        $category = $this->categoryRepository->find($command->categoryId);

        if (!$category instanceof Category) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        if ($category->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        $feed->setTitle($command->title);
        $feed->setCategory($category);
        $feed->setStatus($command->status);
        $feed->setUpdatedAt(new DateTimeImmutable());

        $this->feedRepository->save($feed);
    }
}
