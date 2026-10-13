<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Social\Port\RecapIntelligenceInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class UnconfiguredRecapIntelligence implements RecapIntelligenceInterface
{
    public function configured(): bool
    {
        return false;
    }

    public function generate(string $instructions, string $anonymizedSources): string
    {
        throw new UnrecoverableMessageHandlingException('AI provider setup is pending.');
    }
}
