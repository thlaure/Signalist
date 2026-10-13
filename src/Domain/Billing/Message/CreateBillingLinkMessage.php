<?php

declare(strict_types=1);

namespace App\Domain\Billing\Message;

final readonly class CreateBillingLinkMessage
{
    public function __construct(public string $linkId)
    {
    }
}
