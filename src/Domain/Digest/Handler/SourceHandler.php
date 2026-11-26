<?php

declare(strict_types=1);

namespace App\Domain\Digest\Handler;

use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Digest\Service\SourcePolicy;
use App\Domain\Digest\Service\WeeklyPeriod;
use App\Domain\Feed\Exception\FeedUrlAlreadyExistsException;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Privacy\Exception\ProcessingRestrictedException;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\Organization;
use App\Entity\User;
use App\Entity\WeeklyDigest;

use function count;

use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SourceHandler
{
    public function __construct(private EntityManagerInterface $em, private SourcePolicy $policy, private MessageBusInterface $bus)
    {
    }

    /** @return array<string, mixed> */
    public function list(User $user): array
    {
        return ['items' => array_map(self::output(...), $this->policy->all($user)), 'limit' => $this->policy->limit($user), 'selectionRequired' => $this->policy->needsSelection($user)];
    }

    public function create(User $user, string $url): Feed
    {
        $feed = $this->em->wrapInTransaction(function () use ($user, $url): Feed {
            $this->em->refresh($user, LockMode::PESSIMISTIC_WRITE);

            if ($user->isProcessingRestricted()) {
                throw new ProcessingRestrictedException();
            }
            foreach ($this->policy->all($user) as $existing) {
                if ($existing->getUrl() === $url) {
                    throw new FeedUrlAlreadyExistsException($url);
                }
            }

            if (count($this->policy->selected($user)) >= $this->policy->limit($user)) {
                throw new PlanLimitReachedException('feeds');
            }
            $category = $this->em->getRepository(Category::class)->findOneBy(['owner' => $user, 'organization' => null, 'slug' => 'digest-sources']);

            if (!$category instanceof Category) {
                $category = new Category()->setOwner($user)->setName('Sources')->setSlug('digest-sources');
                $this->em->persist($category);
            }
            $feed = new Feed()->setOwner($user)->setCategory($category)->setUrl($url)->setTitle($url);
            $this->em->persist($feed);

            return $feed;
        });
        $this->bus->dispatch(new CrawlFeedMessage($feed->getId()->toRfc4122()));

        return $feed;
    }

    public function remove(User $user, string $id): void
    {
        $this->em->remove($this->get($user, $id));
        $this->em->flush();
        $this->resumeBlocked($user);
    }

    /** @param list<string> $ids */
    public function select(User $user, array $ids): void
    {
        $this->em->wrapInTransaction(function () use ($user, $ids): void {
            $this->em->refresh($user, LockMode::PESSIMISTIC_WRITE);

            if ($user->isProcessingRestricted()) {
                throw new ProcessingRestrictedException();
            }

            if (count($ids) > $this->policy->limit($user)) {
                throw new PlanLimitReachedException('feeds');
            }
            foreach ($ids as $id) {
                $this->get($user, $id);
            }
            foreach ($this->policy->all($user) as $feed) {
                $feed->setStatus(in_array($feed->getId()->toRfc4122(), $ids, true) ? Feed::STATUS_ACTIVE : Feed::STATUS_PAUSED);
            }
            $this->resumeBlocked($user);
        });
    }

    private function resumeBlocked(User $user): void
    {
        if ($this->policy->needsSelection($user)) {
            return;
        }
        $period = new WeeklyPeriod()->cutoff(new DateTimeImmutable())->format('Y-m-d');
        foreach ($this->em->getRepository(WeeklyDigest::class)->findBy(['owner' => $user, 'period' => $period, 'status' => 'selection_required']) as $digest) {
            $digest->setStatus('queued');
        }
        $this->em->flush();
    }

    private function get(User $user, string $id): Feed
    {
        $feed = Uuid::isValid($id) ? $this->em->find(Feed::class, Uuid::fromString($id)) : null;

        if (!$feed instanceof Feed || $feed->getOrganization() instanceof Organization || 'rss' !== $feed->getSource() || !$feed->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $feed;
    }

    /** @return array{id: string, title: string, url: string, status: string, lastError: ?string} */
    public static function output(Feed $feed): array
    {
        return ['id' => $feed->getId()->toRfc4122(), 'title' => $feed->getTitle(), 'url' => $feed->getUrl(), 'status' => $feed->getStatus(), 'lastError' => $feed->getLastError()];
    }
}
