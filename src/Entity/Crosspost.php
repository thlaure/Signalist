<?php

declare(strict_types=1);

namespace App\Entity;

use function count;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class Crosspost
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $approvedAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $publishedIds = [];

    public function __construct(
        #[ORM\ManyToOne(targetEntity: ExternalAccount::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private ExternalAccount $account,
        #[ORM\Column(length: 255)] private string $remoteAccountId,
        /** @var list<string> */
        #[ORM\Column(type: Types::JSON)] private array $posts,
        #[ORM\Column(length: 255)] private string $recordKey,
    ) {
        $this->id = Uuid::v7();
        $this->approvedAt = new DateTimeImmutable();
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

    public function getApprovedAt(): DateTimeImmutable
    {
        return $this->approvedAt;
    }

    public function getRecordKey(): string
    {
        return $this->recordKey;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /** @return list<string> */
    public function getPosts(): array
    {
        return $this->posts;
    }

    /** @return list<string> */
    public function getPublishedIds(): array
    {
        return $this->publishedIds;
    }

    public function published(string $id): void
    {
        $this->publishedIds[] = $id;
        $this->status = count($this->publishedIds) === count($this->posts) ? 'published' : 'pending';
    }
}
