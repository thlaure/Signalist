<?php

declare(strict_types=1);

namespace App\Domain\Connection\MessageHandler;

use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Entity\ExternalAccount;
use App\Entity\OAuthAttempt;
use App\Infrastructure\Security\TokenCipher;
use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

use function is_array;
use function is_string;

use const JSON_THROW_ON_ERROR;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class ExchangeOAuthCodeMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private OAuthGatewayInterface $gateway, private TokenCipher $cipher, private BlueskyGatewayInterface $bluesky)
    {
    }

    public function __invoke(ExchangeOAuthCodeMessage $message): void
    {
        $attempt = $this->entityManager->find(OAuthAttempt::class, Uuid::fromString($message->attemptId));

        if (!$attempt instanceof OAuthAttempt || 'ready' !== $attempt->getStatus()) {
            return;
        }

        if ($attempt->getOwner()->isDeleted() || $attempt->getOwner()->isProcessingRestricted() || $attempt->getExpiresAt() < new DateTimeImmutable()) {
            $attempt->finish(false);
            $this->entityManager->flush();

            return;
        }

        $this->entityManager->wrapInTransaction(function () use ($attempt): void {
            $this->entityManager->refresh($attempt, LockMode::PESSIMISTIC_WRITE);

            if ('ready' !== $attempt->getStatus()) {
                return;
            }
            $code = $this->cipher->decrypt($attempt->getEncryptedCode() ?? '');

            if ('bluesky' === $attempt->getProvider()) {
                $decoded = json_decode($code, true, flags: JSON_THROW_ON_ERROR);
                $parameters = [];

                if (!is_array($decoded)) {
                    throw new UnrecoverableMessageHandlingException('Invalid authorization response.');
                }
                foreach ($decoded as $key => $value) {
                    if (!is_string($key) || !is_string($value)) {
                        throw new UnrecoverableMessageHandlingException('Invalid authorization response.');
                    } $parameters[$key] = $value;
                }
                $tokens = $this->bluesky->callback($attempt->getOwner()->getId()->toRfc4122(), $attempt->getId()->toRfc4122(), $parameters);
            } else {
                $tokens = $this->gateway->exchange($attempt->getProvider(), $code, $this->cipher->decrypt($attempt->getEncryptedVerifier() ?? ''));
            }
            $account = $this->entityManager->getRepository(ExternalAccount::class)->findOneBy(['owner' => $attempt->getOwner(), 'provider' => $attempt->getProvider()]);

            if (!$account instanceof ExternalAccount) {
                $account = new ExternalAccount($attempt->getOwner(), $attempt->getProvider());
                $this->entityManager->persist($account);
            }
            $account->connected($this->cipher->encrypt($tokens->serialize()), $tokens->expiresAt, $tokens->remoteId);
            $attempt->finish(true);
        });
    }
}
