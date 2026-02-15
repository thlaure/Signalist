<?php

declare(strict_types=1);

namespace App\Domain\Auth\Command;

final readonly class VerifyEmailCommand
{
    public function __construct(
        public string $userId,
        public string $email,
        public int $expiresAt,
        public string $signature,
    ) {
    }
}
