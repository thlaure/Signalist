<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class SeatSyncRequest
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private bool $processed = false;

    public function __construct(#[ORM\ManyToOne(targetEntity: Subscription::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Subscription $subscription)
    {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
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
