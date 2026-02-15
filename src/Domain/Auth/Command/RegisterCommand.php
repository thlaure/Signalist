<?php

declare(strict_types=1);

namespace App\Domain\Auth\Command;

final readonly class RegisterCommand
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
