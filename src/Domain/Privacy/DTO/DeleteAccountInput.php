<?php

declare(strict_types=1);

namespace App\Domain\Privacy\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeleteAccountInput
{
    public function __construct(#[Assert\NotBlank] public string $password)
    {
    }
}
