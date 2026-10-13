<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Spotlight\DTO\CommandInput;
use App\Domain\Spotlight\Handler\ExecuteCommandHandler;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class SpotlightController
{
    public function __construct(private ExecuteCommandHandler $handler)
    {
    }

    #[Route('/api/v1/spotlight', methods: ['POST'])]
    public function __invoke(#[CurrentUser] User $user, #[MapRequestPayload] CommandInput $input): JsonResponse
    {
        return new JsonResponse(['path' => $this->handler->execute($user, $input->command)]);
    }
}
