<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class Organization
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    /** @var Collection<int, OrganizationMember> */
    #[ORM\OneToMany(targetEntity: OrganizationMember::class, mappedBy: 'organization', orphanRemoval: true)]
    private Collection $members;

    #[ORM\Column(length: 20, options: ['default' => 'free'])]
    private string $plan = 'free';

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $paidUntil = null;

    public function __construct(#[ORM\Column(length: 100)] private string $name)
    {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
        $this->members = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPlan(): string
    {
        return $this->paidUntil instanceof DateTimeImmutable && $this->paidUntil > new DateTimeImmutable() ? $this->plan : 'free';
    }

    public function setPlan(string $plan, ?DateTimeImmutable $paidUntil): void
    {
        $this->plan = $plan;
        $this->paidUntil = $paidUntil;
    }

    /** @return Collection<int, OrganizationMember> */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(OrganizationMember $member): void
    {
        if (!$this->members->contains($member)) {
            $this->members[] = $member;
        }
    }

    public function removeMember(OrganizationMember $member): void
    {
        $this->members->removeElement($member);
    }

    public function roleFor(User $user): ?string
    {
        foreach ($this->members as $member) {
            if ($member->getUser()->getId()->equals($user->getId())) {
                return $member->getRole();
            }
        }

        return null;
    }
}
