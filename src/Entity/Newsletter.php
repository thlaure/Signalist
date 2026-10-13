<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
final class Newsletter
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 150)]
    private string $title = '';

    #[ORM\Column]
    private int $readingMinutes = 5;

    #[ORM\Column(nullable: true)]
    private ?int $intervalHours = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $nextRunAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $html = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $generatedAt = null;

    public function __construct(#[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner)
    {
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

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getReadingMinutes(): int
    {
        return $this->readingMinutes;
    }

    public function getIntervalHours(): ?int
    {
        return $this->intervalHours;
    }

    public function getNextRunAt(): ?DateTimeImmutable
    {
        return $this->nextRunAt;
    }

    public function getHtml(): ?string
    {
        return $this->html;
    }

    public function getGeneratedAt(): ?DateTimeImmutable
    {
        return $this->generatedAt;
    }

    public function configure(string $title, int $minutes, ?int $intervalHours): void
    {
        $this->title = $title;
        $this->readingMinutes = $minutes;

        if ($intervalHours !== $this->intervalHours) {
            $this->nextRunAt = null === $intervalHours ? null : new DateTimeImmutable()->modify('+'.$intervalHours.' hours');
        }
        $this->intervalHours = $intervalHours;
    }

    public function generate(string $html): void
    {
        $this->html = $html;
        $this->generatedAt = new DateTimeImmutable();
    }

    public function advanceSchedule(DateTimeImmutable $now): void
    {
        $this->nextRunAt = null === $this->intervalHours ? null : $now->modify('+'.$this->intervalHours.' hours');
    }
}
