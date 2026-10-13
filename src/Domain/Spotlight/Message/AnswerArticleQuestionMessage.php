<?php

declare(strict_types=1);

namespace App\Domain\Spotlight\Message;

final readonly class AnswerArticleQuestionMessage
{
    public function __construct(public string $searchId)
    {
    }
}
