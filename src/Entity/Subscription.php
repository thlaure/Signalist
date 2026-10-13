<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

use function in_array;

use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class Subscription
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 255, unique: true, nullable: true)]
    private ?string $customerId = null;

    #[ORM\Column(length: 20)]
    private string $status = 'inactive';

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(unique: true, nullable: true, onDelete: 'CASCADE')] private ?User $owner = null,
        #[ORM\ManyToOne(targetEntity: Organization::class)] #[ORM\JoinColumn(unique: true, nullable: true, onDelete: 'CASCADE')] private ?Organization $organization = null,
    ) {
        if ($owner instanceof User !== !$organization instanceof Organization) {
            throw new InvalidArgumentException('Exactly one billing scope is required.');
        } $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getCustomerId(): ?string
    {
        return $this->customerId;
    }

    public function setCustomerId(string $id): void
    {
        $this->customerId = $id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function apply(string $status, string $plan, ?DateTimeImmutable $expiresAt): void
    {
        $this->status = $status;
        $active = in_array($status, ['active', 'trialing'], true);
        $this->owner?->setPlan($active ? $plan : 'free', $active ? $expiresAt : null);
        $this->organization?->setPlan($active ? $plan : 'free', $active ? $expiresAt : null);
    }
}
