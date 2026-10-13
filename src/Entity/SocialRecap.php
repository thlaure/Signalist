<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class SocialRecap
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $posts = [];

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 20)] private string $platform,
        #[ORM\Column] private DateTimeImmutable $periodStart,
        #[ORM\Column] private DateTimeImmutable $periodEnd,
        #[ORM\Column] private bool $useAi = false,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function getPeriodStart(): DateTimeImmutable
    {
        return $this->periodStart;
    }

    public function getPeriodEnd(): DateTimeImmutable
    {
        return $this->periodEnd;
    }

    public function usesAi(): bool
    {
        return $this->useAi;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return list<string> */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /** @param list<string> $posts */
    public function draft(array $posts): void
    {
        $this->posts = $posts;
        $this->status = 'draft';
    }

    public function fail(): void
    {
        $this->status = 'failed';
    }
}
