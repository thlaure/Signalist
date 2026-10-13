<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Organization\DTO\InviteMemberInput;
use App\Domain\Organization\DTO\MemberRoleInput;
use App\Domain\Organization\DTO\OrganizationInput;
use App\Domain\Organization\Handler\OrganizationHandler;
use App\Entity\Organization;
use App\Entity\OrganizationActivity;
use App\Entity\OrganizationMember;
use App\Entity\User;

use const DATE_ATOM;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/organizations')]
final readonly class OrganizationController
{
    public function __construct(private OrganizationHandler $handler, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(array_map(fn (Organization $organization): array => $this->handler->output($organization, $user), $this->handler->list($user)));
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] OrganizationInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->create($user, $input->name), $user), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->get($user, $id), $user));
    }

    #[Route('/{id}/members', methods: ['GET'])]
    public function members(#[CurrentUser] User $user, string $id): JsonResponse
    {
        $organization = $this->handler->get($user, $id);
        $manage = 'admin' === $organization->roleFor($user);

        return new JsonResponse(array_values($organization->getMembers()->map(static fn (OrganizationMember $member): array => ['id' => $member->getId()->toRfc4122(), 'name' => $member->getUser()->getDisplayName(), 'email' => $manage ? $member->getUser()->getEmail() : null, 'role' => $member->getRole()])->toArray()));
    }

    #[Route('/{id}/invitations', methods: ['POST'])]
    public function invite(#[CurrentUser] User $user, string $id, #[MapRequestPayload] InviteMemberInput $input): JsonResponse
    {
        $invitation = $this->handler->invite($user, $id, $input->email, $input->role);

        return new JsonResponse(['id' => $invitation->getId()->toRfc4122(), 'status' => 'queued'], Response::HTTP_CREATED);
    }

    #[Route('/invitations/{id}/accept', methods: ['POST'])]
    public function accept(#[CurrentUser] User $user, string $id, Request $request): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->accept($user, $id, $request->query->getString('signature')), $user));
    }

    #[Route('/{id}/members/{memberId}', methods: ['PATCH'])]
    public function role(#[CurrentUser] User $user, string $id, string $memberId, #[MapRequestPayload] MemberRoleInput $input): Response
    {
        $this->handler->changeMember($user, $id, $memberId, $input->role);

        return new Response(status: 204);
    }

    #[Route('/{id}/members/{memberId}', methods: ['DELETE'])]
    public function remove(#[CurrentUser] User $user, string $id, string $memberId): Response
    {
        $this->handler->changeMember($user, $id, $memberId, null);

        return new Response(status: 204);
    }

    #[Route('/{id}/activity', methods: ['GET'])]
    public function activity(#[CurrentUser] User $user, string $id): JsonResponse
    {
        $organization = $this->handler->get($user, $id);
        $activities = $this->entityManager->getRepository(OrganizationActivity::class)->findBy(['organization' => $organization], ['createdAt' => 'DESC'], 100);

        return new JsonResponse(array_map(static fn (OrganizationActivity $activity): array => ['action' => $activity->getAction(), 'actor' => $activity->getActor()?->getDisplayName(), 'createdAt' => $activity->getCreatedAt()->format(DATE_ATOM)], $activities));
    }
}
