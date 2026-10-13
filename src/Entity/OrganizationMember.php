<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['organization_id', 'user_id'])]
final class OrganizationMember
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'members')] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Organization $organization,
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $user,
        #[ORM\Column(length: 20)] private string $role,
    ) {
        $this->id = Uuid::v7();
        $organization->addMember($this);
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }
}
