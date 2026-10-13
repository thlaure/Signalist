<?php

declare(strict_types=1);

namespace App\Domain\Connection\Handler;

use App\Domain\Connection\DTO\AuthorizationStart;
use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\Message\PrepareBlueskyAuthorizationMessage;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Entity\ExternalAccount;
use App\Entity\OAuthAttempt;
use App\Entity\User;
use App\Infrastructure\Security\TokenCipher;

use const DATE_ATOM;

use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

use const JSON_THROW_ON_ERROR;

use function strlen;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class ConnectionHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private OAuthGatewayInterface $gateway, private TokenCipher $cipher, private MessageBusInterface $bus, private BlueskyGatewayInterface $bluesky)
    {
    }

    /** @return list<array<string, mixed>> */
    public function list(User $user): array
    {
        $result = [];
        foreach (['raindrop', 'linkedin', 'x', 'bluesky'] as $provider) {
            $account = $this->entityManager->getRepository(ExternalAccount::class)->findOneBy(['owner' => $user, 'provider' => $provider]);
            $result[] = ['provider' => $provider, 'configured' => $this->cipher->configured() && ('bluesky' === $provider ? $this->bluesky->configured() : $this->gateway->configured($provider)), 'status' => $account?->getStatus() ?? 'disconnected', 'id' => $account?->getId()->toRfc4122(), 'expiresAt' => $account?->getExpiresAt()?->format(DATE_ATOM), 'lastSyncedAt' => $account?->getLastSyncedAt()?->format(DATE_ATOM)];
        }

        return $result;
    }

    public function authorize(User $user, string $provider, ?string $handle = null): AuthorizationStart
    {
        if ($user->isProcessingRestricted() || $user->isDeleted()) {
            throw new AccessDeniedException();
        }

        if (!$this->cipher->configured() || !('bluesky' === $provider ? $this->bluesky->configured() : $this->gateway->configured($provider))) {
            throw new ServiceUnavailableHttpException(null, 'Account provider setup is pending.');
        }

        if ('bluesky' === $provider && (null === $handle || !preg_match('/^(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z]{2,}$/i', $handle))) {
            throw new BadRequestHttpException('A valid Bluesky handle is required.');
        }
        $nonce = bin2hex(random_bytes(32));
        $verifier = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
        $attempt = new OAuthAttempt($user, $provider, hash('sha256', $nonce), $this->cipher->encrypt($verifier));

        if ('bluesky' === $provider) {
            $attempt->prepare($handle ?? '');
        }
        $this->entityManager->persist($attempt);
        $this->entityManager->flush();
        $state = $attempt->getId()->toRfc4122().'.'.$nonce;
        $challenge = rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=');

        if ('bluesky' === $provider) {
            $this->bus->dispatch(new PrepareBlueskyAuthorizationMessage($attempt->getId()->toRfc4122()));

            return new AuthorizationStart(null, $state, $attempt->getId()->toRfc4122());
        }

        return new AuthorizationStart($this->gateway->authorizationUrl($provider, $state, $challenge), $state, $attempt->getId()->toRfc4122());
    }

    public function callback(string $provider, string $cookieState, ?string $queryState, ?string $code, ?string $issuer = null): void
    {
        if ('' === $cookieState || ('bluesky' !== $provider && (null === $queryState || !hash_equals($cookieState, $queryState)))) {
            throw new BadRequestHttpException('Invalid authorization state.');
        }
        $parts = explode('.', $cookieState, 2);
        $id = $parts[0];
        $nonce = $parts[1] ?? '';
        $attempt = Uuid::isValid($id) ? $this->entityManager->find(OAuthAttempt::class, Uuid::fromString($id)) : null;

        if (!$attempt instanceof OAuthAttempt || $attempt->getProvider() !== $provider || !$attempt->matches($nonce) || $attempt->getExpiresAt() < new DateTimeImmutable()) {
            throw new BadRequestHttpException('Authorization link expired or invalid.');
        }

        if ('bluesky' === $provider && null !== $code) {
            if (null === $issuer || null === $queryState) {
                throw new BadRequestHttpException('Invalid authorization response.');
            } $code = json_encode(['code' => $code, 'state' => $queryState, 'iss' => $issuer], JSON_THROW_ON_ERROR);
        }
        $this->entityManager->wrapInTransaction(function () use ($attempt, $code): void {
            $this->entityManager->refresh($attempt, LockMode::PESSIMISTIC_WRITE);

            if ('pending' !== $attempt->getStatus()) {
                throw new BadRequestHttpException('Authorization link already used.');
            }

            if (null === $code || '' === $code || strlen($code) > 10000) {
                $attempt->finish(false);
            } else {
                $attempt->ready($this->cipher->encrypt($code));
            }
        });

        if ('ready' === $attempt->getStatus()) {
            $this->bus->dispatch(new ExchangeOAuthCodeMessage($attempt->getId()->toRfc4122()));
        }
    }

    public function account(User $user, string $id): ExternalAccount
    {
        $account = Uuid::isValid($id) ? $this->entityManager->find(ExternalAccount::class, Uuid::fromString($id)) : null;

        if (!$account instanceof ExternalAccount || !$account->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return $account;
    }

    public function disconnect(User $user, string $id): void
    {
        $account = $this->account($user, $id);

        if ('bluesky' === $account->getProvider()) {
            $this->entityManager->createQuery('DELETE FROM App\\Entity\\OAuthSdkStore s WHERE s.owner = :owner')->setParameter('owner', $user)->execute();
        }
        foreach ($this->entityManager->getRepository(OAuthAttempt::class)->findBy(['owner' => $user, 'provider' => $account->getProvider()]) as $attempt) {
            $this->entityManager->remove($attempt);
        }
        $account->disconnect();
        $this->entityManager->flush();
    }

    /** @return array<string, mixed> */
    public function authorization(User $user, string $id): array
    {
        $attempt = Uuid::isValid($id) ? $this->entityManager->find(OAuthAttempt::class, Uuid::fromString($id)) : null;

        if (!$attempt instanceof OAuthAttempt || !$attempt->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        return ['status' => $attempt->getExpiresAt() < new DateTimeImmutable() ? 'expired' : $attempt->getStatus(), 'url' => $attempt->getAuthorizationUrl()];
    }
}
