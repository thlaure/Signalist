<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class ArticleSearch
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20)]
    private string $status = 'pending';

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $answer = null;

    public function getAnswer(): ?string
    {
        return $this->answer;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $articleIds = [];

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 500)] private string $query,
        #[ORM\Column(length: 20)] private string $mode,
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

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    /** @return list<string> */
    public function getArticleIds(): array
    {
        return $this->articleIds;
    }

    /** @param list<string> $ids */
    public function complete(array $ids): void
    {
        $this->articleIds = $ids;
        $this->status = 'complete';
    }

    public function fail(): void
    {
        $this->status = 'failed';
    }
}
