<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class OAuthAttempt
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $encryptedCode = null;

    #[ORM\Column(length: 253, nullable: true)]
    private ?string $loginHint = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $authorizationUrl = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 20)] private string $provider,
        #[ORM\Column(length: 64)] private string $nonceHash,
        #[ORM\Column(type: Types::TEXT, nullable: true)] private ?string $encryptedVerifier,
    ) {
        $this->id = Uuid::v7();
        $this->expiresAt = new DateTimeImmutable('+10 minutes');
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

    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function matches(string $nonce): bool
    {
        return hash_equals($this->nonceHash, hash('sha256', $nonce));
    }

    public function getEncryptedVerifier(): ?string
    {
        return $this->encryptedVerifier;
    }

    public function getEncryptedCode(): ?string
    {
        return $this->encryptedCode;
    }

    public function ready(string $encryptedCode): void
    {
        $this->encryptedCode = $encryptedCode;
        $this->status = 'ready';
    }

    public function prepare(string $handle): void
    {
        $this->loginHint = $handle;
        $this->status = 'preparing';
    }

    public function getLoginHint(): ?string
    {
        return $this->loginHint;
    }

    public function getAuthorizationUrl(): ?string
    {
        return $this->authorizationUrl;
    }

    public function prepared(string $url): void
    {
        $this->authorizationUrl = $url;
        $this->status = 'pending';
    }

    public function finish(bool $success): void
    {
        $this->encryptedCode = null;
        $this->encryptedVerifier = null;
        $this->status = $success ? 'connected' : 'failed';
    }
}
