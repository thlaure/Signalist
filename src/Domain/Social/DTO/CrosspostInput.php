<?php

declare(strict_types=1);

namespace App\Domain\Social\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CrosspostInput
{
    public function __construct(
        #[Assert\Uuid] public string $accountId,
        #[Assert\NotBlank] #[Assert\Length(max: 20000)] public string $text,
        #[Assert\IsTrue(message: 'Publication must be explicitly approved.')] public bool $confirmed = false,
    ) {
    }
}
