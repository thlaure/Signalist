<?php

declare(strict_types=1);

namespace App\Domain\Billing\MessageHandler;

use App\Domain\Billing\Message\ReconcileBillingEventMessage;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Entity\BillingEvent;
use App\Entity\Subscription;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ReconcileBillingEventMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private BillingGatewayInterface $gateway)
    {
    }

    public function __invoke(ReconcileBillingEventMessage $message): void
    {
        $event = $this->entityManager->find(BillingEvent::class, $message->eventId);

        if (!$event instanceof BillingEvent || $event->isProcessed()) {
            return;
        }
        $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy(['customerId' => $event->getCustomerId()]);

        if (!$subscription instanceof Subscription) {
            $event->complete();
            $this->entityManager->flush();

            return;
        }
        $this->entityManager->wrapInTransaction(function () use ($subscription, $event): void {
            // Serialize reconciliation for this customer, fetching authoritative current state rather than an old webhook snapshot.
            $this->entityManager->refresh($subscription, LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->refresh($event, LockMode::PESSIMISTIC_WRITE);

            if ($event->isProcessed()) {
                return;
            }
            $current = $this->gateway->currentSubscription($event->getCustomerId());
            $subscription->apply($current->status, $current->plan, $current->expiresAt);
            $event->complete();
        });
    }
}
