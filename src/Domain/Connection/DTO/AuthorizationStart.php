<?php

declare(strict_types=1);

namespace App\Domain\Connection\DTO;

final readonly class AuthorizationStart
{
    public function __construct(public ?string $url, public string $state, public string $attemptId)
    {
    }
}
