<?php

declare(strict_types=1);

namespace App\Domain\Organization\Service;

use App\Entity\Organization;
use App\Entity\User;

use function in_array;

final readonly class OrganizationPolicy
{
    public function allows(User $user, Organization $organization, string $action): bool
    {
        if ($user->isDeleted()) {
            return false;
        }
        $role = $organization->roleFor($user);

        if ('read' === $action) {
            return null !== $role;
        }

        if ($user->isProcessingRestricted()) {
            return false;
        }

        return 'manage' === $action ? 'admin' === $role : ('write' === $action && in_array($role, ['admin', 'curator'], true));
    }
}
