<?php

declare(strict_types=1);

namespace App\Infrastructure\Billing;

use App\Domain\Billing\Message\ReconcileBillingEventMessage;
use App\Entity\BillingEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

use function is_string;

use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use UnexpectedValueException;

final readonly class BillingWebhookReceiver
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus, private string $webhookSecret)
    {
    }

    public function receive(string $body, string $signature): void
    {
        if ('' === $this->webhookSecret) {
            throw new ServiceUnavailableHttpException(null, 'Billing setup is pending.');
        }

        try {
            $event = Webhook::constructEvent($body, $signature, $this->webhookSecret);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            throw new BadRequestHttpException('Invalid billing event.');
        }

        if (!str_starts_with($event->type, 'customer.subscription.')) {
            return;
        }
        $data = $event->data->object->toArray();
        $customer = $data['customer'] ?? null;

        if (!is_string($customer) || '' === $customer) {
            throw new BadRequestHttpException('Invalid billing event.');
        }
        $existing = $this->entityManager->find(BillingEvent::class, $event->id);

        if ($existing instanceof BillingEvent && $existing->isProcessed()) {
            return;
        }

        if (!$existing instanceof BillingEvent) {
            try {
                $this->entityManager->persist(new BillingEvent($event->id, $customer));
                $this->entityManager->flush();
            } catch (UniqueConstraintViolationException) {
                return;
            }
        }
        $this->bus->dispatch(new ReconcileBillingEventMessage($event->id));
    }
}
