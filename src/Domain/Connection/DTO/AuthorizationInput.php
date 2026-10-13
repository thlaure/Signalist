<?php

declare(strict_types=1);

namespace App\Domain\Connection\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AuthorizationInput
{
    public function __construct(#[Assert\Length(max: 253)] public ?string $handle = null)
    {
    }
}
