<?php

declare(strict_types=1);

namespace App\Domain\Digest\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SourceSelectionInput
{
    /** @param list<string> $ids */
    public function __construct(#[Assert\Count(max: 20)] #[Assert\Unique] #[Assert\All([new Assert\Uuid()])] public array $ids)
    {
    }
}
