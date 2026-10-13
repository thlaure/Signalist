<?php

declare(strict_types=1);

namespace App\Domain\Connection\MessageHandler;

use App\Domain\Connection\Message\PrepareBlueskyAuthorizationMessage;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Entity\OAuthAttempt;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class PrepareBlueskyAuthorizationMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private BlueskyGatewayInterface $gateway)
    {
    }

    public function __invoke(PrepareBlueskyAuthorizationMessage $message): void
    {
        $attempt = $this->entityManager->find(OAuthAttempt::class, Uuid::fromString($message->attemptId));

        if (!$attempt instanceof OAuthAttempt || 'preparing' !== $attempt->getStatus()) {
            return;
        }

        if ($attempt->getExpiresAt() < new DateTimeImmutable() || $attempt->getOwner()->isDeleted() || $attempt->getOwner()->isProcessingRestricted()) {
            $attempt->finish(false);
            $this->entityManager->flush();

            return;
        }
        $url = $this->gateway->authorize($attempt->getOwner()->getId()->toRfc4122(), $message->attemptId, $attempt->getLoginHint() ?? '');
        $attempt->prepared($url);
        $this->entityManager->flush();
    }
}
