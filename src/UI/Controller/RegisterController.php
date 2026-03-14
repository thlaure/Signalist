<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Auth\Command\RegisterCommand;
use App\Domain\Auth\DTO\Input\RegisterInput;
use App\Domain\Auth\Handler\RegisterHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RegisterController
{
    public function __construct(
        private RegisterHandler $handler,
    ) {
    }

    #[Route('/api/v1/auth/register', methods: [Request::METHOD_POST])]
    public function __invoke(#[MapRequestPayload] RegisterInput $input): JsonResponse
    {
        ($this->handler)(new RegisterCommand(
            email: $input->email,
            password: $input->password,
        ));

        return new JsonResponse(
            ['message' => 'If this email address is not already registered, you will receive a verification email shortly.'],
            Response::HTTP_CREATED,
        );
    }
}
