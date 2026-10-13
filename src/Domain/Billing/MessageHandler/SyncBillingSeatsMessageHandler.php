<?php

declare(strict_types=1);

namespace App\Domain\Billing\MessageHandler;

use App\Domain\Billing\Message\SyncBillingSeatsMessage;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Entity\Organization;
use App\Entity\SeatSyncRequest;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class SyncBillingSeatsMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private BillingGatewayInterface $gateway)
    {
    }

    public function __invoke(SyncBillingSeatsMessage $message): void
    {
        $request = $this->entityManager->find(SeatSyncRequest::class, Uuid::fromString($message->requestId));

        if (!$request instanceof SeatSyncRequest || $request->isProcessed()) {
            return;
        }
        $this->entityManager->wrapInTransaction(function () use ($request, $message): void {
            $subscription = $request->getSubscription();
            $this->entityManager->refresh($subscription, LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->refresh($request, LockMode::PESSIMISTIC_WRITE);

            if ($request->isProcessed()) {
                return;
            }
            $organization = $subscription->getOrganization();

            if ($organization instanceof Organization && null !== $subscription->getCustomerId()) {
                $this->gateway->updateSeats($subscription->getCustomerId(), max(1, $organization->getMembers()->count()), $message->requestId);
            }
            $request->complete();
        });
    }
}
