<?php

declare(strict_types=1);

namespace App\Domain\Digest\Service;

use App\Domain\Billing\Service\PlanCatalog;
use App\Entity\Feed;
use App\Entity\User;

use function count;

use Doctrine\ORM\EntityManagerInterface;

final readonly class SourcePolicy
{
    public function __construct(private EntityManagerInterface $em, private PlanCatalog $plans)
    {
    }

    /** @return list<Feed> */
    public function all(User $owner): array
    {
        return $this->em->getRepository(Feed::class)->findBy(['owner' => $owner, 'organization' => null, 'source' => 'rss'], ['createdAt' => 'ASC', 'id' => 'ASC']);
    }

    public function limit(User $owner): int
    {
        return $this->plans->limits($owner->getPlan())['feeds'];
    }

    /** @return list<Feed> */
    public function selected(User $owner): array
    {
        return array_values(array_filter($this->all($owner), static fn (Feed $feed): bool => Feed::STATUS_PAUSED !== $feed->getStatus()));
    }

    public function needsSelection(User $owner): bool
    {
        return count($this->selected($owner)) > $this->limit($owner);
    }

    /** @return list<Feed> */
    public function active(User $owner): array
    {
        return $this->needsSelection($owner) ? [] : $this->selected($owner);
    }
}
