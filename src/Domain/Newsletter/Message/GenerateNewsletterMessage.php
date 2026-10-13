<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Message;

final readonly class GenerateNewsletterMessage
{
    public function __construct(public string $runId)
    {
    }
}
