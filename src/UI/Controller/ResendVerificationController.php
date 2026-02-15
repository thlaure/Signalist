<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Auth\Command\ResendVerificationCommand;
use App\Domain\Auth\DTO\Input\ResendVerificationInput;
use App\Domain\Auth\Handler\ResendVerificationHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ResendVerificationController
{
    public function __construct(
        private ResendVerificationHandler $handler,
    ) {
    }

    #[Route('/api/v1/auth/resend-verification', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] ResendVerificationInput $input): JsonResponse
    {
        ($this->handler)(new ResendVerificationCommand(
            email: $input->email,
        ));

        return new JsonResponse(['sent' => true], Response::HTTP_OK);
    }
}
