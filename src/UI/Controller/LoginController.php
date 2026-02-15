<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Auth\Command\LoginCommand;
use App\Domain\Auth\DTO\Input\LoginInput;
use App\Domain\Auth\Handler\LoginHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

final readonly class LoginController
{
    public function __construct(
        private LoginHandler $handler,
    ) {
    }

    #[Route('/api/v1/auth/login', methods: ['POST'])]
    public function __invoke(#[MapRequestPayload] LoginInput $input): JsonResponse
    {
        $result = ($this->handler)(new LoginCommand(
            email: $input->email,
            password: $input->password,
        ));

        return new JsonResponse($result, Response::HTTP_OK);
    }
}
