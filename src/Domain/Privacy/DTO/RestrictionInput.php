<?php

declare(strict_types=1);

namespace App\Domain\Privacy\DTO;

final readonly class RestrictionInput
{
    public function __construct(public bool $restricted)
    {
    }
}
