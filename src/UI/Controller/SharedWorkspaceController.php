<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Category\DTO\Input\CreateCategoryInput;
use App\Domain\Category\DTO\Input\UpdateCategoryInput;
use App\Domain\Category\DTO\Output\CategoryOutput;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\DTO\Input\UpdateFeedInput;
use App\Domain\Feed\DTO\Output\FeedOutput;
use App\Domain\Organization\DTO\AnnotationInput;
use App\Domain\Organization\DTO\ReadStateInput;
use App\Domain\Organization\DTO\SharedBookmarkInput;
use App\Domain\Organization\Handler\SharedWorkspaceHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/organizations/{organizationId}')]
final readonly class SharedWorkspaceController
{
    public function __construct(private SharedWorkspaceHandler $handler)
    {
    }

    #[Route('/categories', methods: ['GET'])]
    public function categories(#[CurrentUser] User $user, string $organizationId): JsonResponse
    {
        return new JsonResponse($this->handler->categories($user, $organizationId));
    }

    #[Route('/categories', methods: ['POST'])]
    public function createCategory(#[CurrentUser] User $user, string $organizationId, #[MapRequestPayload] CreateCategoryInput $input): JsonResponse
    {
        return new JsonResponse(CategoryOutput::fromEntity($this->handler->createCategory($user, $organizationId, $input)), Response::HTTP_CREATED);
    }

    #[Route('/categories/{id}', methods: ['PUT'])]
    public function updateCategory(#[CurrentUser] User $user, string $organizationId, string $id, #[MapRequestPayload] UpdateCategoryInput $input): JsonResponse
    {
        return new JsonResponse(CategoryOutput::fromEntity($this->handler->updateCategory($user, $organizationId, $id, $input)));
    }

    #[Route('/categories/{id}', methods: ['DELETE'])]
    public function deleteCategory(#[CurrentUser] User $user, string $organizationId, string $id): Response
    {
        $this->handler->deleteCategory($user, $organizationId, $id);

        return new Response(status: 204);
    }

    #[Route('/feeds', methods: ['GET'])]
    public function feeds(#[CurrentUser] User $user, string $organizationId): JsonResponse
    {
        return new JsonResponse($this->handler->feeds($user, $organizationId));
    }

    #[Route('/feeds', methods: ['POST'])]
    public function createFeed(#[CurrentUser] User $user, string $organizationId, #[MapRequestPayload] AddFeedInput $input): JsonResponse
    {
        return new JsonResponse(FeedOutput::fromEntity($this->handler->createFeed($user, $organizationId, $input)), Response::HTTP_CREATED);
    }

    #[Route('/feeds/{id}', methods: ['PUT'])]
    public function updateFeed(#[CurrentUser] User $user, string $organizationId, string $id, #[MapRequestPayload] UpdateFeedInput $input): JsonResponse
    {
        return new JsonResponse(FeedOutput::fromEntity($this->handler->updateFeed($user, $organizationId, $id, $input)));
    }

    #[Route('/feeds/{id}', methods: ['DELETE'])]
    public function deleteFeed(#[CurrentUser] User $user, string $organizationId, string $id): Response
    {
        $this->handler->deleteFeed($user, $organizationId, $id);

        return new Response(status: 204);
    }

    #[Route('/articles', methods: ['GET'])]
    public function articles(#[CurrentUser] User $user, string $organizationId, Request $request): JsonResponse
    {
        return new JsonResponse($this->handler->articles($user, $organizationId, $request->query->getInt('page', 1), $request->query->getString('search')));
    }

    #[Route('/articles/{id}', methods: ['GET'])]
    public function article(#[CurrentUser] User $user, string $organizationId, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->articleOutput($user, $this->handler->article($user, $organizationId, $id)));
    }

    #[Route('/articles/{id}/read', methods: ['PATCH'])]
    public function read(#[CurrentUser] User $user, string $organizationId, string $id, #[MapRequestPayload] ReadStateInput $input): Response
    {
        $this->handler->markRead($user, $organizationId, $id, $input->isRead);

        return new Response(status: 204);
    }

    #[Route('/bookmarks', methods: ['GET'])]
    public function bookmarks(#[CurrentUser] User $user, string $organizationId): JsonResponse
    {
        return new JsonResponse($this->handler->bookmarks($user, $organizationId));
    }

    #[Route('/bookmarks', methods: ['POST'])]
    public function bookmark(#[CurrentUser] User $user, string $organizationId, #[MapRequestPayload] SharedBookmarkInput $input): JsonResponse
    {
        return new JsonResponse(['id' => $this->handler->bookmark($user, $organizationId, $input->articleId)->getId()->toRfc4122()], Response::HTTP_CREATED);
    }

    #[Route('/bookmarks/{id}/annotations', methods: ['POST'])]
    public function annotate(#[CurrentUser] User $user, string $organizationId, string $id, #[MapRequestPayload] AnnotationInput $input): Response
    {
        $this->handler->annotate($user, $organizationId, $id, $input->body);

        return new Response(status: 201);
    }

    #[Route('/bookmarks/{id}', methods: ['DELETE'])]
    public function removeBookmark(#[CurrentUser] User $user, string $organizationId, string $id): Response
    {
        $this->handler->removeBookmark($user, $organizationId, $id);

        return new Response(status: 204);
    }
}
