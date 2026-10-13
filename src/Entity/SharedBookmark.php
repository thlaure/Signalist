<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['organization_id', 'article_id'])]
final class SharedBookmark
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    #[ORM\Column]
    private DateTimeImmutable $createdAt;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Organization::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Organization $organization,
        #[ORM\ManyToOne(targetEntity: Article::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Article $article,
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')] private ?User $curator,
    ) {
        $this->id = Uuid::v7();
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getArticle(): Article
    {
        return $this->article;
    }

    public function getCurator(): ?User
    {
        return $this->curator;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }
}
