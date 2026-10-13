<?php

declare(strict_types=1);

namespace App\Domain\Billing\DTO;

use DateTimeImmutable;

final readonly class BillingSnapshot
{
    public function __construct(public string $status, public string $plan, public ?DateTimeImmutable $expiresAt)
    {
    }
}
