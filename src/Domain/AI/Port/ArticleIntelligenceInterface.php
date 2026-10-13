<?php

declare(strict_types=1);

namespace App\Domain\AI\Port;

interface ArticleIntelligenceInterface
{
    public function configured(): bool;

    public function summarize(string $anonymizedText): string;

    /** @return list<string> */
    public function tags(string $anonymizedText): array;

    /** @return list<float> */
    public function embed(string $anonymizedText): array;
}
