<?php

declare(strict_types=1);

namespace App\Domain\Social\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RecapEditInput
{
    public function __construct(#[Assert\NotBlank] #[Assert\Length(max: 20000)] public string $text)
    {
    }
}
