<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class InviteMemberInput
{
    public function __construct(
        #[Assert\NotBlank] #[Assert\Email] #[Assert\Length(max: 180)] public string $email,
        #[Assert\Choice(['admin', 'curator', 'reader'])] public string $role = 'reader',
    ) {
    }
}
