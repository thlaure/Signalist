<?php

declare(strict_types=1);

namespace App\Domain\Billing\Message;

final readonly class ReconcileBillingEventMessage
{
    public function __construct(public string $eventId)
    {
    }
}
