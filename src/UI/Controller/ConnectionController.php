<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Connection\DTO\AuthorizationInput;
use App\Domain\Connection\Handler\ConnectionHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/connections')]
final readonly class ConnectionController
{
    public function __construct(private ConnectionHandler $handler)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse($this->handler->list($user), headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('/{provider}/authorize', requirements: ['provider' => 'raindrop|linkedin|x|bluesky'], methods: ['POST'])]
    public function authorize(#[CurrentUser] User $user, string $provider, Request $request, #[MapRequestPayload] AuthorizationInput $input): JsonResponse
    {
        $start = $this->handler->authorize($user, $provider, $input->handle);
        $response = new JsonResponse(['url' => $start->url, 'attemptId' => $start->attemptId], null === $start->url ? 202 : 200, ['Cache-Control' => 'no-store']);
        $response->headers->setCookie(Cookie::create('signalist_oauth_'.$provider, $start->state, time() + 600, '/api/v1/connections/'.$provider.'/callback', secure: $request->isSecure(), httpOnly: true, sameSite: Cookie::SAMESITE_LAX));

        return $response;
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function disconnect(#[CurrentUser] User $user, string $id): Response
    {
        $this->handler->disconnect($user, $id);

        return new Response(status: 204);
    }

    #[Route('/authorizations/{id}', methods: ['GET'])]
    public function authorization(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse($this->handler->authorization($user, $id), headers: ['Cache-Control' => 'no-store']);
    }
}
