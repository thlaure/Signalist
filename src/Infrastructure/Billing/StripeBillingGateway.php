<?php

declare(strict_types=1);

namespace App\Infrastructure\Billing;

use App\Domain\Billing\DTO\BillingSnapshot;
use App\Domain\Billing\Port\BillingGatewayInterface;
use DateTimeImmutable;

use function in_array;
use function is_int;
use function is_string;

use Stripe\StripeClient;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class StripeBillingGateway implements BillingGatewayInterface
{
    public function __construct(private string $secretKey, private string $teamPriceId, private string $enterprisePriceId, private string $frontendUrl, private string $webhookSecret)
    {
    }

    public function configured(): bool
    {
        return !in_array('', [$this->secretKey, $this->teamPriceId, $this->webhookSecret], true);
    }

    public function customer(string $scopeId): string
    {
        return $this->client()->customers->create(['metadata' => ['signalist_scope' => $scopeId]], ['idempotency_key' => 'customer-'.$scopeId])->id;
    }

    public function link(string $kind, string $customerId, int $seats, string $requestId): string
    {
        $return = rtrim($this->frontendUrl, '/').'/billing';

        if ('portal' === $kind) {
            return $this->client()->billingPortal->sessions->create(['customer' => $customerId, 'return_url' => $return], ['idempotency_key' => 'portal-'.$requestId])->url;
        }
        $session = $this->client()->checkout->sessions->create([
            'mode' => 'subscription', 'customer' => $customerId,
            'line_items' => [['price' => $this->teamPriceId, 'quantity' => max(1, $seats)]],
            'success_url' => $return.'?status=success', 'cancel_url' => $return.'?status=cancelled',
            'client_reference_id' => $requestId,
        ], ['idempotency_key' => 'checkout-'.$requestId]);

        if (!is_string($session->url)) {
            throw new UnrecoverableMessageHandlingException('Billing link unavailable.');
        }

        return $session->url;
    }

    public function currentSubscription(string $customerId): BillingSnapshot
    {
        $subscriptions = $this->client()->subscriptions->all(['customer' => $customerId, 'status' => 'all', 'limit' => 100]);
        $latest = null;
        foreach ($subscriptions->data as $subscription) {
            if (!in_array($subscription->status, ['active', 'trialing'], true)) {
                continue;
            }
            foreach ($subscription->items->data as $item) {
                $plan = $item->price->id === $this->teamPriceId ? 'team' : ('' !== $this->enterprisePriceId && $item->price->id === $this->enterprisePriceId ? 'enterprise' : null);

                if (null === $plan) {
                    continue;
                }
                $end = $item->current_period_end ?? $subscription->current_period_end ?? null;

                if (!is_int($end)) {
                    continue;
                }
                $snapshot = new BillingSnapshot($subscription->status, $plan, new DateTimeImmutable()->setTimestamp($end));

                if (!$latest instanceof BillingSnapshot || $snapshot->expiresAt > $latest->expiresAt) {
                    $latest = $snapshot;
                }
            }
        }

        return $latest ?? new BillingSnapshot('inactive', 'free', null);
    }

    private function client(): StripeClient
    {
        if (!$this->configured()) {
            throw new UnrecoverableMessageHandlingException('Billing is not configured.');
        }

        return new StripeClient($this->secretKey);
    }

    public function updateSeats(string $customerId, int $seats, string $requestId): void
    {
        foreach ($this->client()->subscriptions->all(['customer' => $customerId, 'status' => 'all', 'limit' => 100])->data as $subscription) {
            if (!in_array($subscription->status, ['active', 'trialing'], true)) {
                continue;
            }
            foreach ($subscription->items->data as $item) {
                if (!in_array($item->price->id, array_filter([$this->teamPriceId, $this->enterprisePriceId]), true)) {
                    continue;
                }
                $this->client()->subscriptionItems->update($item->id, ['quantity' => $seats, 'proration_behavior' => 'create_prorations'], ['idempotency_key' => 'seats-'.$requestId.'-'.$item->id]);
            }
        }
    }
}
