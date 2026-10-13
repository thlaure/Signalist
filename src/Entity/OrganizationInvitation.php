<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class OrganizationInvitation
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $acceptedAt = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Organization::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Organization $organization,
        #[ORM\Column(length: 180)] private string $email,
        #[ORM\Column(length: 20)] private string $role,
    ) {
        $this->id = Uuid::v7();
        $this->expiresAt = new DateTimeImmutable('+7 days');
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isAccepted(): bool
    {
        return $this->acceptedAt instanceof DateTimeImmutable;
    }

    public function accept(): void
    {
        $this->acceptedAt = new DateTimeImmutable();
    }
}
