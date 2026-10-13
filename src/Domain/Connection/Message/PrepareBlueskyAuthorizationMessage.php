<?php

declare(strict_types=1);

namespace App\Domain\Connection\Message;

final readonly class PrepareBlueskyAuthorizationMessage
{
    public function __construct(public string $attemptId)
    {
    }
}
