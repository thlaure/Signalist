<?php

declare(strict_types=1);

namespace App\Domain\Social\Port;

interface RecapIntelligenceInterface
{
    public function configured(): bool;

    public function generate(string $instructions, string $anonymizedSources): string;
}
