<?php

declare(strict_types=1);

namespace App\Domain\Privacy\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ConsentInput
{
    public function __construct(
        #[Assert\Choice(['external_ai', 'newsletter', 'marketing'])]
        public string $purpose,
        public bool $granted,
    ) {
    }
}
