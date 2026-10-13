<?php

declare(strict_types=1);

namespace App\Infrastructure\AI;

use App\Domain\Spotlight\Port\ArticleQuestionInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class UnconfiguredArticleQuestions implements ArticleQuestionInterface
{
    public function configured(): bool
    {
        return false;
    }

    public function answer(string $anonymizedQuestion, string $anonymizedSources): string
    {
        throw new UnrecoverableMessageHandlingException('AI provider setup is pending.');
    }
}
