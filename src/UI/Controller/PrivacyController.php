<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Privacy\DTO\ConsentInput;
use App\Domain\Privacy\DTO\DeleteAccountInput;
use App\Domain\Privacy\DTO\ProfileInput;
use App\Domain\Privacy\DTO\RestrictionInput;
use App\Domain\Privacy\Handler\PrivacyHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/user')]
final readonly class PrivacyController
{
    public function __construct(private PrivacyHandler $handler)
    {
    }

    #[Route('/profile', methods: ['GET'])]
    public function profile(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse($this->handler->profile($user), headers: ['Cache-Control' => 'no-store']);
    }

    #[Route('/profile', methods: ['PUT'])]
    public function update(#[CurrentUser] User $user, #[MapRequestPayload] ProfileInput $input): JsonResponse
    {
        $this->handler->updateProfile($user, $input->displayName);

        return new JsonResponse($this->handler->profile($user));
    }

    #[Route('/consent', methods: ['POST'])]
    public function consent(#[CurrentUser] User $user, #[MapRequestPayload] ConsentInput $input): Response
    {
        $this->handler->consent($user, $input->purpose, $input->granted);

        return new Response(status: 204);
    }

    #[Route('/restrict-processing', methods: ['POST'])]
    public function restrict(#[CurrentUser] User $user, #[MapRequestPayload] RestrictionInput $input): Response
    {
        $this->handler->restrict($user, $input->restricted);

        return new Response(status: 204);
    }

    #[Route('/opt-out', methods: ['POST'])]
    public function optOut(#[CurrentUser] User $user): Response
    {
        $this->handler->restrict($user, true);

        return new Response(status: 204);
    }

    #[Route('/data-export', methods: ['GET'])]
    public function export(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse($this->handler->export($user), headers: [
            'Cache-Control' => 'no-store', 'Content-Disposition' => 'attachment; filename="signalist-export.json"',
        ]);
    }

    #[Route('/account', methods: ['DELETE'])]
    public function delete(#[CurrentUser] User $user, #[MapRequestPayload] DeleteAccountInput $input): Response
    {
        $this->handler->delete($user, $input->password);

        return new Response(status: 204);
    }
}
