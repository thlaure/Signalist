<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\UniqueConstraint(columns: ['owner_id', 'kind', 'store_key'])]
final class OAuthSdkStore
{
    #[ORM\Id] #[ORM\Column(type: 'uuid')]
    private Uuid $id;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)] #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')] private User $owner,
        #[ORM\Column(length: 20)] private string $kind,
        #[ORM\Column(length: 1000)] private string $storeKey,
        #[ORM\Column(type: Types::TEXT)] private string $encryptedValue,
        #[ORM\Column] private DateTimeImmutable $expiresAt,
    ) {
        $this->id = Uuid::v7();
    }
}
