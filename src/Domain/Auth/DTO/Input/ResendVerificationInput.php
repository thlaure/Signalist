<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTO\Input;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ResendVerificationInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
    ) {
    }
}
