<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Organization\Service\OrganizationPolicy;
use App\Entity\Organization;
use App\Entity\User;

use function in_array;
use function strlen;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, Organization> */
final class OrganizationVoter extends Voter
{
    public function __construct(private readonly OrganizationPolicy $policy)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Organization && in_array($attribute, ['organization.read', 'organization.write', 'organization.manage'], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return $user instanceof User && $this->policy->allows($user, $subject, substr($attribute, strlen('organization.')));
    }
}
