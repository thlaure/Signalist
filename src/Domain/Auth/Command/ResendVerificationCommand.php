<?php

declare(strict_types=1);

namespace App\Domain\Auth\Command;

final readonly class ResendVerificationCommand
{
    public function __construct(
        public string $email,
    ) {
    }
}
