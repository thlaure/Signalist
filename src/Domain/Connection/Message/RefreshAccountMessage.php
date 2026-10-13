<?php

declare(strict_types=1);

namespace App\Domain\Connection\Message;

final readonly class RefreshAccountMessage
{
    public function __construct(public string $accountId)
    {
    }
}
