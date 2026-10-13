<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['scope_key', 'metric', 'period'])]
final class UsageCounter
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct(
        #[ORM\Column(length: 60)] private string $scopeKey,
        #[ORM\Column(length: 30)] private string $metric,
        #[ORM\Column(length: 10)] private string $period,
        #[ORM\Column] private int $used = 0,
    ) {
        $this->id = Uuid::v7();
    }

    public function getUsed(): int
    {
        return $this->used;
    }
}
