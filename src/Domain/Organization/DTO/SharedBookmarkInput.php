<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SharedBookmarkInput
{
    public function __construct(#[Assert\Uuid] public string $articleId)
    {
    }
}
