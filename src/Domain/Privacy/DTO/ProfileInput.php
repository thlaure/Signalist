<?php

declare(strict_types=1);

namespace App\Domain\Privacy\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ProfileInput
{
    public function __construct(
        #[Assert\Length(max: 100)]
        public string $displayName,
    ) {
    }
}
