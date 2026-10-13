<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class NewsletterInput
{
    public function __construct(
        #[Assert\NotBlank] #[Assert\Length(max: 150)] public string $title,
        #[Assert\Range(min: 1, max: 60)] public int $readingMinutes = 5,
        #[Assert\Range(min: 1, max: 8760)] public ?int $intervalHours = null,
    ) {
    }
}
