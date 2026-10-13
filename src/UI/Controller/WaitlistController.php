<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Waitlist\DTO\JoinWaitlistInput;
use App\Domain\Waitlist\Handler\WaitlistHandler;
use App\Entity\WaitlistEntry;
use App\Infrastructure\Waitlist\WaitlistToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api/v1/waitlist')]
final readonly class WaitlistController
{
    public function __construct(private WaitlistHandler $handler, private EntityManagerInterface $entityManager, private WaitlistToken $token, private RateLimiterFactory $waitlistLimiter)
    {
    }

    #[Route('', methods: ['POST'])]
    public function join(#[MapRequestPayload] JoinWaitlistInput $input, Request $request): JsonResponse
    {
        $limit = $this->waitlistLimiter->create(hash('sha256', $request->getClientIp() ?? 'unknown'))->consume();

        if (!$limit->isAccepted()) {
            throw new TooManyRequestsHttpException(max(1, $limit->getRetryAfter()->getTimestamp() - time()), 'Please retry later.');
        }
        $this->handler->join($input->email);

        return new JsonResponse(['message' => 'If eligible, you will receive a confirmation email.'], Response::HTTP_ACCEPTED);
    }

    #[Route('/{id}/{action}', requirements: ['action' => 'confirm|withdraw'], methods: ['POST'])]
    public function action(string $id, string $action, Request $request): Response
    {
        $signature = $request->query->getString('signature');
        $entry = Uuid::isValid($id) ? $this->entityManager->find(WaitlistEntry::class, Uuid::fromString($id)) : null;

        if (!$entry instanceof WaitlistEntry || !$this->token->valid($entry, $action, $signature)) {
            throw new BadRequestHttpException('Invalid waitlist link.');
        }

        if ('confirm' === $action) {
            if ($entry->getCreatedAt() < new DateTimeImmutable('-7 days')) {
                throw new BadRequestHttpException('Confirmation link expired.');
            }
            $entry->confirm();
        } else {
            $this->entityManager->remove($entry);
        }
        $this->entityManager->flush();

        return new Response(status: 204);
    }
}
