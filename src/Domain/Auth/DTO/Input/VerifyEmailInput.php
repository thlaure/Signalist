<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTO\Input;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class VerifyEmailInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Uuid]
        public string $userId,

        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,

        #[Assert\NotBlank]
        #[Assert\Positive]
        public int $expiresAt,

        #[Assert\NotBlank]
        public string $signature,
    ) {
    }
}
