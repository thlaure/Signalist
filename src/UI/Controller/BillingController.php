<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Billing\DTO\BillingLinkInput;
use App\Domain\Billing\Handler\BillingHandler;
use App\Entity\User;

use function is_string;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/billing')]
final readonly class BillingController
{
    public function __construct(private BillingHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function status(#[CurrentUser] User $user, Request $request): JsonResponse
    {
        $organization = $request->query->get('organizationId');

        return new JsonResponse($this->handler->status($user, is_string($organization) ? $organization : null));
    }

    #[Route('/links', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] BillingLinkInput $input): JsonResponse
    {
        $link = $this->handler->createLink($user, $input->kind, $input->organizationId);

        return new JsonResponse(['id' => $link->getId()->toRfc4122(), 'status' => $link->getStatus()], Response::HTTP_ACCEPTED);
    }

    #[Route('/links/{id}', methods: ['GET'])]
    public function link(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->link($user, $id), headers: ['Cache-Control' => 'no-store']);
    }
}
