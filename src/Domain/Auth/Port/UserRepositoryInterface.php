<?php

declare(strict_types=1);

namespace App\Domain\Auth\Port;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function find(string $id): ?User;

    public function findByEmail(string $email): ?User;
}
