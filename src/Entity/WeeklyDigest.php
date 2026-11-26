<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uniq_digest_owner_period', columns: ['owner_id', 'period'])]
final class WeeklyDigest
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 20)]
    private string $status = 'queued';

    #[ORM\Column]
    private int $attempts = 0;

    /** @var list<array{title: string, url: string, source: string, summary: string}> */
    #[ORM\Column(type: Types::JSON)]
    private array $items = [];

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $sentAt = null;

    /** Lease written by the weekly dispatcher; a digest is re-queued only after it expires. */
    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $dispatchedAt = null;

    #[ORM\Column]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 10)] private string $period,
    ) {
        $this->id = Uuid::v7();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getPeriod(): string
    {
        return $this->period;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getAttempts(): int
    {
        return $this->attempts;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDispatchedAt(): ?DateTimeImmutable
    {
        return $this->dispatchedAt;
    }

    public function getSentAt(): ?DateTimeImmutable
    {
        return $this->sentAt;
    }

    /** @return list<array{title: string, url: string, source: string, summary: string}> */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function attempt(): void
    {
        ++$this->attempts;
        $this->setStatus('generating');
    }

    /** @param list<array{title: string, url: string, source: string, summary: string}> $items */
    public function complete(array $items): void
    {
        $this->items = $items;
        $this->setStatus('ready');
    }

    public function sent(): void
    {
        $this->sentAt = new DateTimeImmutable();
        $this->setStatus('sent');
    }
}
