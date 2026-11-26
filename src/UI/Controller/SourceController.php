<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Digest\DTO\SourceInput;
use App\Domain\Digest\DTO\SourceSelectionInput;
use App\Domain\Digest\Handler\SourceHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/sources')]
final readonly class SourceController
{
    public function __construct(private SourceHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse($this->handler->list($user));
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] SourceInput $input): JsonResponse
    {
        return new JsonResponse(SourceHandler::output($this->handler->create($user, trim($input->url))), Response::HTTP_CREATED);
    }

    #[Route('/selection', methods: ['PUT'])]
    public function select(#[CurrentUser] User $user, #[MapRequestPayload] SourceSelectionInput $input): Response
    {
        $this->handler->select($user, $input->ids);

        return new Response(status: 204);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function remove(#[CurrentUser] User $user, string $id): Response
    {
        $this->handler->remove($user, $id);

        return new Response(status: 204);
    }
}
