<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Search\DTO\SearchInput;
use App\Domain\Search\Handler\SearchHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/search')]
final readonly class SearchController
{
    public function __construct(private SearchHandler $handler)
    {
    }

    #[Route('', methods: ['POST'])]
    public function create(#[CurrentUser] User $user, #[MapRequestPayload] SearchInput $input): JsonResponse
    {
        $search = $this->handler->create($user, $input);

        return new JsonResponse($this->handler->get($user, $search->getId()->toRfc4122()), 'complete' === $search->getStatus() ? 200 : 202, ['Cache-Control' => 'no-store']);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->get($user, $id), headers: ['Cache-Control' => 'no-store']);
    }
}
