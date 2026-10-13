<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Newsletter\DTO\NewsletterInput;
use App\Domain\Newsletter\Handler\NewsletterHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/newsletters')]
final readonly class NewsletterController
{
    public function __construct(private NewsletterHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(array_map($this->handler->output(...), $this->handler->list($user)));
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] NewsletterInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->save($user, $input)), Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->get($user, $id)));
    }

    #[Route('/{id}', methods: ['PUT'])]
    public function update(#[CurrentUser] User $user, string $id, #[MapRequestPayload] NewsletterInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->save($user, $input, $id)));
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, string $id): Response
    {
        $this->handler->delete($user, $id);

        return new Response(status: 204);
    }

    #[Route('/{id}/{action}', requirements: ['action' => 'generate|send'], methods: ['POST'])]
    public function generate(#[CurrentUser] User $user, string $id, string $action): JsonResponse
    {
        $run = $this->handler->generate($user, $id, 'send' === $action);

        return new JsonResponse(['runId' => $run->getId()->toRfc4122(), 'status' => $run->getStatus()], Response::HTTP_ACCEPTED);
    }
}
