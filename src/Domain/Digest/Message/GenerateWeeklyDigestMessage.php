<?php

declare(strict_types=1);

namespace App\Domain\Digest\Message;

final readonly class GenerateWeeklyDigestMessage
{
    public function __construct(public string $digestId)
    {
    }
}
