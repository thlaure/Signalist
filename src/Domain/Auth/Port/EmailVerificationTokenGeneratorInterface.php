<?php

declare(strict_types=1);

namespace App\Domain\Auth\Port;

interface EmailVerificationTokenGeneratorInterface
{
    public function generateSignedUrl(string $userId, string $email): string;

    public function validateSignedUrl(string $userId, string $email, int $expiresAt, string $signature): bool;
}
