<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['newsletter_id', 'period'])]
final class NewsletterRun
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20)]
    private string $status = 'queued';

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Newsletter::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Newsletter $newsletter,
        #[ORM\Column(length: 50)] private string $period,
        #[ORM\Column] private bool $deliver,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    public function shouldDeliver(): bool
    {
        return $this->deliver;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}
