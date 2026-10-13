<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AnnotationInput
{
    public function __construct(#[Assert\NotBlank(normalizer: 'trim')] #[Assert\Length(max: 5000)] public string $body)
    {
    }
}
