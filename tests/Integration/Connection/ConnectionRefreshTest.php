<?php

declare(strict_types=1);

namespace App\Tests\Integration\Connection;

use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Handler\ConnectionHandler;
use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\Message\PrepareBlueskyAuthorizationMessage;
use App\Domain\Connection\MessageHandler\ExchangeOAuthCodeMessageHandler;
use App\Domain\Connection\MessageHandler\PrepareBlueskyAuthorizationMessageHandler;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Domain\Connection\Service\AccountTokens;
use App\Entity\ExternalAccount;
use App\Entity\User;
use App\Infrastructure\Security\TokenCipher;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

final class ConnectionRefreshTest extends DatabaseTestCase
{
    public function testRefreshKeepsExistingRefreshTokenWhenRotationIsOmitted(): void
    {
        $owner = $this->user();
        $cipher = new TokenCipher(base64_encode(random_bytes(32)));
        $account = new ExternalAccount($owner, 'x');
        $account->connected($cipher->encrypt(new OAuthTokens('old-access', 'old-refresh', new DateTimeImmutable('-1 minute'), 'remote')->serialize()), new DateTimeImmutable('-1 minute'), 'remote');
        $this->entityManager->persist($account);
        $this->entityManager->flush();
        $gateway = $this->createMock(OAuthGatewayInterface::class);
        $gateway->expects($this->once())->method('refresh')->with('x', 'old-refresh', 'remote')->willReturn(new OAuthTokens('new-access', null, new DateTimeImmutable('+1 hour'), 'remote'));
        $tokens = new AccountTokens($this->entityManager, $gateway, $cipher);
        $this->assertSame('new-access', $tokens->accessToken($account, 'remote'));
        $this->assertSame('new-access', $tokens->accessToken($account, 'remote'));
        $stored = json_decode($cipher->decrypt($account->getEncryptedTokens() ?? ''), true);
        $this->assertIsArray($stored);
        $this->assertSame('old-refresh', $stored['refreshToken']);
        $owner->setProcessingRestricted(true);
        $this->entityManager->flush();
        $this->expectException(UnrecoverableMessageHandlingException::class);
        $tokens->accessToken($account, 'remote');
    }

    public function testBlueskyPreparationCallbackAndDisconnectUseOwnerBoundAttempt(): void
    {
        $owner = $this->user();
        $cipher = new TokenCipher(base64_encode(random_bytes(32)));
        $gateway = $this->createMock(OAuthGatewayInterface::class);
        $bluesky = $this->createMock(BlueskyGatewayInterface::class);
        $bluesky->method('configured')->willReturn(true);
        $bluesky->expects($this->once())->method('authorize')->willReturn('https://auth.example.com/authorize');
        $bluesky->expects($this->once())->method('callback')->willReturn(new OAuthTokens('sdk-managed-session', null, new DateTimeImmutable('+1 hour'), 'did:plc:fixture'));
        $handler = new ConnectionHandler($this->entityManager, $gateway, $cipher, self::getContainer()->get(MessageBusInterface::class), $bluesky);
        $start = $handler->authorize($owner, 'bluesky', 'alice.bsky.social');
        $this->assertNull($start->url);
        new PrepareBlueskyAuthorizationMessageHandler($this->entityManager, $bluesky)(new PrepareBlueskyAuthorizationMessage($start->attemptId));
        $this->assertSame('https://auth.example.com/authorize', $handler->authorization($owner, $start->attemptId)['url']);
        $handler->callback('bluesky', $start->state, 'sdk-state', 'sdk-code', 'https://bsky.social');
        new ExchangeOAuthCodeMessageHandler($this->entityManager, $gateway, $cipher, $bluesky)(new ExchangeOAuthCodeMessage($start->attemptId));
        $this->assertSame('connected', $handler->authorization($owner, $start->attemptId)['status']);
        $account = $this->entityManager->getRepository(ExternalAccount::class)->findOneBy(['owner' => $owner, 'provider' => 'bluesky']);
        $this->assertInstanceOf(ExternalAccount::class, $account);
        $handler->disconnect($owner, $account->getId()->toRfc4122());
        $this->assertNull($account->getEncryptedTokens());
        $this->assertSame('disconnected', $account->getStatus());
    }

    private function user(): User
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
