<?php

declare(strict_types=1);

namespace App\Domain\AI\Message;

final readonly class EnrichArticleMessage
{
    public function __construct(public string $articleId)
    {
    }
}
