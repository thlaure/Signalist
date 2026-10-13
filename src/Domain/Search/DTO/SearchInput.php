<?php

declare(strict_types=1);

namespace App\Domain\Search\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SearchInput
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(max: 500)]
        public string $query,
        #[Assert\Choice(['fulltext', 'semantic', 'hybrid', 'question'])]
        public string $mode = 'hybrid',
    ) {
    }
}
