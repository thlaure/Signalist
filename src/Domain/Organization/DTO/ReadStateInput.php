<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTO;

final readonly class ReadStateInput
{
    public function __construct(public bool $isRead)
    {
    }
}
