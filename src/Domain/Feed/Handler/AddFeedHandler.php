<?php

declare(strict_types=1);

namespace App\Domain\Feed\Handler;

use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Category\Exception\CategoryNotFoundException;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Command\AddFeedCommand;
use App\Domain\Feed\Exception\FeedUrlAlreadyExistsException;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Feed\Port\FeedRepositoryInterface;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Entity\User;

use function count;

use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class AddFeedHandler
{
    public function __construct(
        private FeedRepositoryInterface $feedRepository,
        private CategoryRepositoryInterface $categoryRepository,
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $messageBus,
        private PlanCatalog $plans = new PlanCatalog(),
    ) {
    }

    public function __invoke(AddFeedCommand $command): string
    {
        $user = $this->userRepository->find($command->ownerId);

        if (!$user instanceof User) {
            throw new RuntimeException('User not found');
        }

        if ($this->feedRepository->findByUrlAndOwner($command->url, $command->ownerId) instanceof Feed) {
            throw new FeedUrlAlreadyExistsException($command->url);
        }

        $limit = $this->plans->limits($user->getPlan())['feeds'];

        if ($limit >= 0 && count(array_filter($this->feedRepository->findAllByOwner($command->ownerId), static fn (Feed $feed): bool => 'rss' === $feed->getSource())) >= $limit) {
            throw new PlanLimitReachedException('feeds');
        }

        $category = $this->categoryRepository->find($command->categoryId);

        if (!$category instanceof Category) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        if ($category->getOrganization() instanceof Organization || $category->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new CategoryNotFoundException($command->categoryId);
        }

        $feed = new Feed();
        $feed->setUrl($command->url);
        $feed->setTitle($command->title ?? $command->url);
        $feed->setCategory($category);
        $feed->setOwner($user);

        $this->feedRepository->save($feed);

        $this->messageBus->dispatch(new CrawlFeedMessage($feed->getId()->toRfc4122()));

        return $feed->getId()->toRfc4122();
    }
}
