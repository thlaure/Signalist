<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class BookmarkSyncRun
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(length: 20)]
    private string $phase = 'import';

    #[ORM\Column]
    private int $page = 0;

    #[ORM\Column]
    private int $processed = 0;

    #[ORM\Column(name: 'cursor_offset')]
    private int $offset = 0;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: ExternalAccount::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private ExternalAccount $account,
        #[ORM\Column] private bool $includeNotes,
        #[ORM\Column(length: 255)] private string $remoteAccountId,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getAccount(): ExternalAccount
    {
        return $this->account;
    }

    public function getRemoteAccountId(): string
    {
        return $this->remoteAccountId;
    }

    public function includesNotes(): bool
    {
        return $this->includeNotes;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getPhase(): string
    {
        return $this->phase;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getProcessed(): int
    {
        return $this->processed;
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function advanceOffset(): void
    {
        ++$this->offset;
    }

    public function increment(): void
    {
        ++$this->processed;
    }

    public function nextPage(): void
    {
        ++$this->page;
        $this->offset = 0;
    }

    public function exportPhase(): void
    {
        $this->phase = 'export';
        $this->page = 0;
        $this->offset = 0;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
