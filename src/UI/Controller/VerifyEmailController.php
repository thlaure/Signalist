<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Auth\Command\VerifyEmailCommand;
use App\Domain\Auth\DTO\Input\VerifyEmailInput;
use App\Domain\Auth\Handler\VerifyEmailHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class VerifyEmailController
{
    public function __construct(
        private VerifyEmailHandler $handler,
    ) {
    }

    #[Route('/api/v1/auth/verify-email', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] VerifyEmailInput $input): JsonResponse
    {
        ($this->handler)(new VerifyEmailCommand(
            userId: $input->userId,
            email: $input->email,
            expiresAt: $input->expiresAt,
            signature: $input->signature,
        ));

        return new JsonResponse(['verified' => true], Response::HTTP_OK);
    }
}
