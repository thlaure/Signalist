<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['article_id', 'user_id'])]
final class ArticleReadState
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Article::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private Article $article,
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $user,
        #[ORM\Column] private bool $isRead,
    ) {
        $this->id = Uuid::v7();
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $read): void
    {
        $this->isRead = $read;
    }
}
