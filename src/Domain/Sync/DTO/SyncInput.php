<?php

declare(strict_types=1);

namespace App\Domain\Sync\DTO;

final readonly class SyncInput
{
    public function __construct(public bool $includeNotes = false)
    {
    }
}
