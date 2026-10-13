<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class OrganizationActivity
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Organization::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Organization $organization,
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?User $actor,
        #[ORM\Column(length: 40)] private string $action,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getActor(): ?User
    {
        return $this->actor;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
