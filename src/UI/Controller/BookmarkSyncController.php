<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Sync\DTO\SyncInput;
use App\Domain\Sync\Handler\BookmarkSyncHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class BookmarkSyncController
{
    public function __construct(private BookmarkSyncHandler $handler)
    {
    }

    #[Route('/api/v1/connections/{id}/sync', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, string $id, #[MapRequestPayload] SyncInput $input): JsonResponse
    {
        $run = $this->handler->create($user, $id, $input->includeNotes);

        return new JsonResponse($this->handler->get($user, $run->getId()->toRfc4122()), Response::HTTP_ACCEPTED);
    }

    #[Route('/api/v1/sync/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->get($user, $id));
    }
}
