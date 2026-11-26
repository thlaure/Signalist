<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Digest\Handler\DigestHandler;
use App\Domain\Digest\Port\DigestIntelligenceInterface;
use App\Domain\Digest\Service\WeeklyPeriod;
use App\Entity\User;

use const DATE_ATOM;

use DateTimeImmutable;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/v1/digests')]
final readonly class DigestController
{
    public function __construct(private DigestHandler $handler, private DigestIntelligenceInterface $ai, private WeeklyPeriod $period)
    {
    }

    #[Route('', methods: ['GET'])]
    public function list(#[CurrentUser] User $user): JsonResponse
    {
        return new JsonResponse(['items' => array_map(DigestHandler::output(...), $this->handler->list($user)), 'configured' => $this->ai->configured(), 'nextRunAt' => $this->period->cutoff(new DateTimeImmutable())->modify('+1 week')->format(DATE_ATOM)]);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function get(#[CurrentUser] User $user, string $id): JsonResponse
    {
        return new JsonResponse(DigestHandler::output($this->handler->get($user, $id)));
    }
}
