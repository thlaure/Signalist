<?php

declare(strict_types=1);

namespace App\Domain\Social\Message;

final readonly class GenerateSocialRecapMessage
{
    public function __construct(public string $recapId)
    {
    }
}
