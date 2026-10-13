<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class BillingLink
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(length: 2048, nullable: true)]
    private ?string $url = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Subscription::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Subscription $subscription,
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $requestedBy,
        #[ORM\Column(length: 20)] private string $kind,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getSubscription(): Subscription
    {
        return $this->subscription;
    }

    public function getRequestedBy(): User
    {
        return $this->requestedBy;
    }

    public function getKind(): string
    {
        return $this->kind;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function complete(string $url): void
    {
        $this->url = $url;
        $this->status = 'complete';
    }

    public function fail(): void
    {
        $this->status = 'failed';
    }
}
