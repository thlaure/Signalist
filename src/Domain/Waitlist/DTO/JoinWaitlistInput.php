<?php

declare(strict_types=1);

namespace App\Domain\Waitlist\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class JoinWaitlistInput
{
    public function __construct(
        #[Assert\Email] #[Assert\NotBlank] #[Assert\Length(max: 180)] public string $email,
        #[Assert\IsTrue] public bool $consent,
    ) {
    }
}
