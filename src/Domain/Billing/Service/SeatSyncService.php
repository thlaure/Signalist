<?php

declare(strict_types=1);

namespace App\Domain\Billing\Service;

use App\Entity\Organization;
use App\Entity\SeatSyncRequest;
use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;

final readonly class SeatSyncService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function queue(Organization $organization): void
    {
        $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy(['organization' => $organization]);

        if ($subscription instanceof Subscription && null !== $subscription->getCustomerId()) {
            $this->entityManager->persist(new SeatSyncRequest($subscription));
        }
    }
}
