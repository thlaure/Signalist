<?php

declare(strict_types=1);

namespace App\Domain\Social\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RecapInput
{
    public function __construct(
        #[Assert\Choice(['linkedin', 'x', 'instagram', 'bluesky'])] public string $platform,
        #[Assert\Date] public string $start,
        #[Assert\Date] public string $end,
        public bool $useAi = false,
    ) {
    }
}
