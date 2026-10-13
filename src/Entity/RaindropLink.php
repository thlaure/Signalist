<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['account_id', 'remote_account_id', 'remote_id'])]
final class RaindropLink
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column(length: 64)]
    private string $localHash = '';

    #[ORM\Column(length: 64)]
    private string $remoteHash = '';

    #[ORM\Column(options: ['default' => false])]
    private bool $notesIncluded = false;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: ExternalAccount::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private ExternalAccount $account,
        #[ORM\ManyToOne(targetEntity: Article::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Article $article,
        #[ORM\Column(length: 255)] private string $remoteAccountId,
        #[ORM\Column(length: 255)] private string $remoteId,
    ) {
        $this->id = Uuid::v7();
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getLocalHash(): string
    {
        return $this->localHash;
    }

    public function getRemoteHash(): string
    {
        return $this->remoteHash;
    }

    public function getRemoteId(): string
    {
        return $this->remoteId;
    }

    public function includesNotes(): bool
    {
        return $this->notesIncluded;
    }

    public function snapshot(string $localHash, string $remoteHash, bool $notesIncluded = false): void
    {
        $this->localHash = $localHash;
        $this->remoteHash = $remoteHash;
        $this->notesIncluded = $notesIncluded;
    }
}
