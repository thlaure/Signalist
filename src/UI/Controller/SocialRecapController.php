<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Social\DTO\RecapEditInput;
use App\Domain\Social\DTO\RecapInput;
use App\Domain\Social\Handler\SocialRecapHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/social-recaps')]
final readonly class SocialRecapController
{
    public function __construct(private SocialRecapHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(array_map($this->handler->output(...), $this->handler->list($user)), headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] RecapInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->create($user, $input)), Response::HTTP_ACCEPTED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->get($user, $id)), headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function edit(#[CurrentUser] User $user, string $id, #[MapRequestPayload] RecapEditInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->edit($user, $id, $input->text)));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, string $id): Response
    {
        $this->handler->delete($user, $id);

        return new Response(status: 204);
    }
}
