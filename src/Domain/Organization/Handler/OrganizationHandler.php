<?php

declare(strict_types=1);

namespace App\Domain\Organization\Handler;

use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\SeatSyncService;
use App\Domain\Organization\Message\SendInvitationMessage;
use App\Domain\Organization\Service\OrganizationPolicy;
use App\Entity\Organization;
use App\Entity\OrganizationActivity;
use App\Entity\OrganizationInvitation;
use App\Entity\OrganizationMember;
use App\Entity\User;
use App\Infrastructure\Organization\InvitationToken;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class OrganizationHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private OrganizationPolicy $policy, private InvitationToken $token, private MessageBusInterface $bus, private PlanCatalog $plans = new PlanCatalog(), private ?SeatSyncService $seatSync = null)
    {
    }

    /** @return list<Organization> */
    public function list(User $user): array
    {
        /** @var list<Organization> $result */
        $result = $this->entityManager->createQuery('SELECT o FROM '.Organization::class.' o JOIN o.members m WHERE m.user = :user')->setParameter('user', $user)->getResult();

        return $result;
    }

    public function create(User $user, string $name): Organization
    {
        if ($user->isDeleted() || $user->isProcessingRestricted()) {
            throw new AccessDeniedException();
        }
        $organization = new Organization(trim($name));
        $member = new OrganizationMember($organization, $user, 'admin');
        $this->entityManager->persist($organization);
        $this->entityManager->persist($member);
        $this->activity($organization, $user, 'workspace_created');

        return $organization;
    }

    public function get(User $user, string $id, string $action = 'read'): Organization
    {
        $organization = Uuid::isValid($id) ? $this->entityManager->find(Organization::class, Uuid::fromString($id)) : null;

        if (!$organization instanceof Organization || null === $organization->roleFor($user)) {
            throw new NotFoundHttpException();
        }

        if (!$this->policy->allows($user, $organization, $action)) {
            throw new AccessDeniedException();
        }

        return $organization;
    }

    public function invite(User $user, string $id, string $email, string $role): OrganizationInvitation
    {
        $organization = $this->get($user, $id, 'manage');
        $this->memberLimit($organization);
        $invitation = new OrganizationInvitation($organization, mb_strtolower(trim($email)), $role);
        $this->entityManager->persist($invitation);
        $this->activity($organization, $user, 'member_invited');
        $this->bus->dispatch(new SendInvitationMessage($invitation->getId()->toRfc4122()));

        return $invitation;
    }

    public function accept(User $user, string $id, string $signature): Organization
    {
        $invitation = Uuid::isValid($id) ? $this->entityManager->find(OrganizationInvitation::class, Uuid::fromString($id)) : null;

        if (!$invitation instanceof OrganizationInvitation || !hash_equals($this->token->sign($invitation), $signature)
            || $invitation->getExpiresAt() < new DateTimeImmutable() || mb_strtolower($user->getEmail()) !== $invitation->getEmail() || !$user->isEmailVerified()) {
            throw new AccessDeniedException('Invitation unavailable.');
        }
        $organization = $invitation->getOrganization();
        $this->entityManager->wrapInTransaction(function () use ($organization, $invitation, $user): void {
            $this->entityManager->refresh($organization, LockMode::PESSIMISTIC_WRITE);
            $this->entityManager->refresh($invitation, LockMode::PESSIMISTIC_WRITE);

            if ($invitation->isAccepted()) {
                if (null === $organization->roleFor($user)) {
                    throw new AccessDeniedException('Invitation unavailable.');
                }

                return;
            }

            if (null === $organization->roleFor($user)) {
                $this->memberLimit($organization);
                $this->entityManager->persist(new OrganizationMember($organization, $user, $invitation->getRole()));
            }
            $invitation->accept();
            $this->seatSync?->queue($organization);
            $this->entityManager->persist(new OrganizationActivity($organization, $user, 'member_joined'));
        });

        return $organization;
    }

    public function changeMember(User $user, string $organizationId, string $memberId, ?string $role): void
    {
        $organization = $this->get($user, $organizationId, 'manage');
        $this->entityManager->wrapInTransaction(function () use ($organization, $memberId, $role, $user): void {
            $this->entityManager->refresh($organization, LockMode::PESSIMISTIC_WRITE);
            $member = null;
            $admins = 0;
            foreach ($organization->getMembers() as $candidate) {
                $this->entityManager->refresh($candidate, LockMode::PESSIMISTIC_WRITE);

                if ('admin' === $candidate->getRole()) {
                    ++$admins;
                }

                if ($candidate->getId()->toRfc4122() === $memberId) {
                    $member = $candidate;
                }
            }

            if (!$this->policy->allows($user, $organization, 'manage')) {
                throw new AccessDeniedException();
            }

            if (!$member instanceof OrganizationMember) {
                throw new NotFoundHttpException();
            }

            if ('admin' === $member->getRole() && 'admin' !== $role && $admins <= 1) {
                throw new ConflictHttpException('A workspace must retain an administrator.');
            }

            if (null === $role) {
                $organization->removeMember($member);
                $this->entityManager->remove($member);
            } else {
                $member->setRole($role);
            }

            if (null === $role) {
                $this->seatSync?->queue($organization);
            }
            $this->entityManager->persist(new OrganizationActivity($organization, $user, null === $role ? 'member_removed' : 'role_changed'));
        });
    }

    public function activity(Organization $organization, User $user, string $action): void
    {
        $this->entityManager->persist(new OrganizationActivity($organization, $user, $action));
        $this->entityManager->flush();
    }

    private function memberLimit(Organization $organization): void
    {
        $limit = $this->plans->limits($organization->getPlan())['members'];

        if ($limit >= 0 && $organization->getMembers()->count() >= $limit) {
            throw new PlanLimitReachedException('members');
        }
    }

    /** @return array<string, mixed> */
    public function output(Organization $organization, User $user): array
    {
        return ['id' => $organization->getId()->toRfc4122(), 'name' => $organization->getName(), 'role' => $organization->roleFor($user)];
    }
}
