<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Tag\DTO\ArticleTagsInput;
use App\Domain\Tag\DTO\TagInput;
use App\Domain\Tag\Handler\TagHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class TagController
{
    public function __construct(private TagHandler $handler)
    {
    }

    #[Route('/api/v1/tags', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(array_map($this->handler->output(...), $this->handler->list($user)));
    }

    #[Route('/api/v1/tags', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] TagInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->save($user, $input->name)), Response::HTTP_CREATED);
    }

    #[Route('/api/v1/tags/{id}', methods: ['PUT'])]
    public function update(#[CurrentUser] User $user, string $id, #[MapRequestPayload] TagInput $input): JsonResponse
    {
        return new JsonResponse($this->handler->output($this->handler->save($user, $input->name, $id)));
    }

    #[Route('/api/v1/tags/{id}', methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, string $id): Response
    {
        $this->handler->delete($user, $id);

        return new Response(status: 204);
    }

    #[Route('/api/v1/tags/{id}/articles', methods: ['GET'])]
    public function articles(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->articles($user, $id));
    }

    #[Route('/api/v1/articles/{id}/tags', methods: ['PUT'])]
    public function assign(#[CurrentUser] User $user, string $id, #[MapRequestPayload] ArticleTagsInput $input): Response
    {
        $this->handler->assign($user, $id, $input->tags);

        return new Response(status: 204);
    }
}
