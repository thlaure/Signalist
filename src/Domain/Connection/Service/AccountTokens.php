<?php

declare(strict_types=1);

namespace App\Domain\Connection\Service;

use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Entity\ExternalAccount;
use App\Infrastructure\Security\TokenCipher;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

use function in_array;
use function is_array;
use function is_string;

use const JSON_THROW_ON_ERROR;

use RuntimeException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class AccountTokens
{
    public function __construct(private EntityManagerInterface $entityManager, private OAuthGatewayInterface $gateway, private TokenCipher $cipher)
    {
    }

    public function accessToken(ExternalAccount $account, ?string $expectedRemoteId = null): string
    {
        $connection = $this->entityManager->getConnection();
        $key = 'account-tokens-'.$account->getId()->toRfc4122();

        if (!in_array($connection->fetchOne('SELECT pg_try_advisory_lock(hashtextextended(:key, 0))', ['key' => $key]), [true, 1, 't'], true)) {
            throw new RuntimeException('Account refresh already running.');
        }

        try {
            return $this->load($account, $expectedRemoteId);
        } finally {
            $connection->executeQuery('SELECT pg_advisory_unlock(hashtextextended(:key, 0))', ['key' => $key]);
        }
    }

    private function load(ExternalAccount $account, ?string $expectedRemoteId): string
    {
        $owner = $account->getOwner();
        $this->entityManager->refresh($owner);
        $this->entityManager->refresh($account);

        if ($owner->isDeleted() || $owner->isProcessingRestricted() || 'connected' !== $account->getStatus() || null === $account->getEncryptedTokens()) {
            throw new UnrecoverableMessageHandlingException('Account authorization unavailable.');
        }

        if (null !== $expectedRemoteId && $account->getRemoteId() !== $expectedRemoteId) {
            throw new UnrecoverableMessageHandlingException('Connected identity changed.');
        }
        $data = json_decode($this->cipher->decrypt($account->getEncryptedTokens()), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data) || !is_string($data['accessToken'] ?? null)) {
            throw new UnrecoverableMessageHandlingException('Account authorization unavailable.');
        }

        if ($account->getExpiresAt() instanceof DateTimeImmutable && $account->getExpiresAt() <= new DateTimeImmutable('+1 minute')) {
            $refresh = $data['refreshToken'] ?? null;

            if (!is_string($refresh)) {
                $account->requireAuthorization();
                $this->entityManager->flush();

                throw new UnrecoverableMessageHandlingException('Reconnect your account.');
            }

            try {
                $tokens = $this->gateway->refresh($account->getProvider(), $refresh, $account->getRemoteId() ?? '');
            } catch (UnrecoverableMessageHandlingException) {
                $account->requireAuthorization();
                $this->entityManager->flush();

                throw new UnrecoverableMessageHandlingException('Reconnect your account.');
            }

            if ($tokens->remoteId !== $account->getRemoteId()) {
                $account->requireAuthorization();
                $this->entityManager->flush();

                throw new UnrecoverableMessageHandlingException('Connected identity changed.');
            }
            $tokens = new OAuthTokens($tokens->accessToken, $tokens->refreshToken ?? $refresh, $tokens->expiresAt, $tokens->remoteId);
            $account->connected($this->cipher->encrypt($tokens->serialize()), $tokens->expiresAt, $tokens->remoteId);
            $this->entityManager->flush();

            return $tokens->accessToken;
        }

        return $data['accessToken'];
    }
}
