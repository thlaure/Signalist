<?php

declare(strict_types=1);

namespace App\Domain\Auth\Message;

final readonly class SendVerificationEmailMessage
{
    public function __construct(
        public string $userId,
        public string $email,
    ) {
    }
}
