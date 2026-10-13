<?php

declare(strict_types=1);

namespace App\Domain\Spotlight\Port;

interface ArticleQuestionInterface
{
    public function configured(): bool;

    /** Sources are minimized, anonymized text with numbered citations. */
    public function answer(string $anonymizedQuestion, string $anonymizedSources): string;
}
