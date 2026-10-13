<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class BillingEvent
{
    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column]
    private bool $processed = false;

    public function __construct(
        #[ORM\Id] #[ORM\Column(length: 255)] private string $id,
        #[ORM\Column(length: 255)] private string $customerId,
    ) {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): string
    {
        return $this->customerId;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function complete(): void
    {
        $this->processed = true;
    }
}
