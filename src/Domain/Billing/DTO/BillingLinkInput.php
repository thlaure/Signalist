<?php

declare(strict_types=1);

namespace App\Domain\Billing\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class BillingLinkInput
{
    public function __construct(#[Assert\Choice(['checkout', 'portal'])] public string $kind = 'checkout', #[Assert\Uuid] public ?string $organizationId = null)
    {
    }
}
