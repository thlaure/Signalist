<?php

declare(strict_types=1);

namespace App\Domain\Organization\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class MemberRoleInput
{
    public function __construct(#[Assert\Choice(['admin', 'curator', 'reader'])] public string $role)
    {
    }
}
