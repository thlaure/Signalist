<?php

declare(strict_types=1);

namespace App\Domain\Billing\Message;

final readonly class SyncBillingSeatsMessage
{
    public function __construct(public string $requestId)
    {
    }
}
