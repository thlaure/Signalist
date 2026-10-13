<?php

declare(strict_types=1);

namespace App\Domain\Search\Message;

final readonly class RunArticleSearchMessage
{
    public function __construct(public string $searchId)
    {
    }
}
