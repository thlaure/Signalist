<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\AI\Port\ArticleIntelligenceInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class UnconfiguredArticleIntelligence implements ArticleIntelligenceInterface
{
    public function configured(): bool
    {
        return false;
    }

    public function summarize(string $anonymizedText): string
    {
        throw new UnrecoverableMessageHandlingException('AI provider is not configured.');
    }

    public function tags(string $anonymizedText): array
    {
        throw new UnrecoverableMessageHandlingException('AI provider is not configured.');
    }

    public function embed(string $anonymizedText): array
    {
        throw new UnrecoverableMessageHandlingException('AI provider is not configured.');
    }
}
