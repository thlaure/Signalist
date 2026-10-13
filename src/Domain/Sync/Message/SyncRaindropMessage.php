<?php

declare(strict_types=1);

namespace App\Domain\Sync\Message;

final readonly class SyncRaindropMessage
{
    public function __construct(public string $runId)
    {
    }
}
