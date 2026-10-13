<?php

declare(strict_types=1);

namespace App\Domain\Sync\Service;

use const JSON_THROW_ON_ERROR;

final readonly class SyncConflictResolver
{
    public function direction(string $savedLocal, string $savedRemote, string $local, string $remote): string
    {
        if ($savedLocal !== $local) {
            return 'local';
        }

        return $savedRemote !== $remote ? 'remote' : 'none';
    }

    public function hash(string $url, string $title, ?string $note): string
    {
        return hash('sha256', json_encode([$url, $title, $note], JSON_THROW_ON_ERROR));
    }
}
