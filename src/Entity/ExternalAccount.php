<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['owner_id', 'provider'])]
final class ExternalAccount
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $encryptedTokens = null;

    #[ORM\Column(length: 20)]
    private string $status = 'disconnected';

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $remoteId = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $lastSyncedAt = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 20)] private string $provider,
    ) {
        $this->id = Uuid::v7();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getEncryptedTokens(): ?string
    {
        return $this->encryptedTokens;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getRemoteId(): ?string
    {
        return $this->remoteId;
    }

    public function getLastSyncedAt(): ?DateTimeImmutable
    {
        return $this->lastSyncedAt;
    }

    public function connected(string $ciphertext, DateTimeImmutable $expiresAt, string $remoteId): void
    {
        $this->encryptedTokens = $ciphertext;
        $this->expiresAt = $expiresAt;
        $this->remoteId = $remoteId;
        $this->status = 'connected';
    }

    public function disconnect(): void
    {
        $this->encryptedTokens = null;
        $this->expiresAt = null;
        $this->remoteId = null;
        $this->status = 'disconnected';
    }

    public function requireAuthorization(): void
    {
        $this->status = 'reauthorize';
        $this->encryptedTokens = null;
    }

    public function synced(): void
    {
        $this->lastSyncedAt = new DateTimeImmutable();
    }
}
