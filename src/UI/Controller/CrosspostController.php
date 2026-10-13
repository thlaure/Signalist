<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Social\DTO\CrosspostInput;
use App\Domain\Social\Handler\CrosspostHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/crossposts')]
final readonly class CrosspostController
{
    public function __construct(private CrosspostHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(array_map($this->handler->output(...), $this->handler->list($user)), headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] CrosspostInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->create($user, $input)), Response::HTTP_ACCEPTED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->get($user, $id)), headers: ['Cache-Control' => 'no-store']);
    }
}
