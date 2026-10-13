<?php

declare(strict_types=1);

namespace App\Domain\Billing\MessageHandler;

use App\Domain\Billing\Message\CreateBillingLinkMessage;
use App\Domain\Billing\Message\DispatchBillingJobsMessage;
use App\Domain\Billing\Message\ReconcileBillingEventMessage;
use App\Domain\Billing\Message\SyncBillingSeatsMessage;
use App\Entity\BillingEvent;
use App\Entity\BillingLink;
use App\Entity\SeatSyncRequest;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DispatchBillingJobsMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus)
    {
    }

    public function __invoke(DispatchBillingJobsMessage $message): void
    {
        foreach ($this->entityManager->getRepository(BillingLink::class)->findBy(['status' => 'pending'], [], 100) as $link) {
            $this->bus->dispatch(new CreateBillingLinkMessage($link->getId()->toRfc4122()));
        }
        foreach ($this->entityManager->getRepository(BillingEvent::class)->findBy(['processed' => false], [], 100) as $event) {
            $this->bus->dispatch(new ReconcileBillingEventMessage($event->getId()));
        }
        foreach ($this->entityManager->getRepository(SeatSyncRequest::class)->findBy(['processed' => false], [], 100) as $request) {
            $this->bus->dispatch(new SyncBillingSeatsMessage($request->getId()->toRfc4122()));
        }
    }
}
