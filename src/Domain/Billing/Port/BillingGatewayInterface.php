<?php

declare(strict_types=1);

namespace App\Domain\Billing\Port;

use App\Domain\Billing\DTO\BillingSnapshot;

interface BillingGatewayInterface
{
    public function configured(): bool;

    public function customer(string $scopeId): string;

    public function link(string $kind, string $customerId, int $seats, string $requestId): string;

    public function currentSubscription(string $customerId): BillingSnapshot;

    public function updateSeats(string $customerId, int $seats, string $requestId): void;
}
