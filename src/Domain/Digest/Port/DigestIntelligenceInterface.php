<?php

declare(strict_types=1);

namespace App\Domain\Digest\Port;

interface DigestIntelligenceInterface
{
    public function configured(): bool;

    /** @param list<array{title: string, text: string}> $articles
     * @return list<string> summaries in the same order, plain text only
     */
    public function summarize(array $articles): array;
}
