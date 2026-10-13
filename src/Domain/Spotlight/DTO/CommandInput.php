<?php

declare(strict_types=1);

namespace App\Domain\Spotlight\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CommandInput
{
    public function __construct(#[Assert\NotBlank] #[Assert\Length(max: 500)] public string $command)
    {
    }
}
