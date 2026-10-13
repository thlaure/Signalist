<?php

declare(strict_types=1);

namespace App\Domain\Tag\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class TagInput
{
    public function __construct(#[Assert\NotBlank] #[Assert\Length(max: 60)] public string $name)
    {
    }
}
