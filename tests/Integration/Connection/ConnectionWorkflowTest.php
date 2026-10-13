<?php

declare(strict_types=1);

namespace App\Tests\Integration\Connection;

use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Handler\ConnectionHandler;
use App\Domain\Connection\Message\ExchangeOAuthCodeMessage;
use App\Domain\Connection\MessageHandler\ExchangeOAuthCodeMessageHandler;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Entity\ExternalAccount;
use App\Entity\OAuthAttempt;
use App\Entity\User;
use App\Infrastructure\Security\TokenCipher;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

use const JSON_THROW_ON_ERROR;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

final class ConnectionWorkflowTest extends DatabaseTestCase
{
    public function testCallbackStateEncryptionExchangeAndOwnerIsolation(): void
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $this->entityManager->persist($user);
        $this->entityManager->persist($other);
        $this->entityManager->flush();
        $cipher = new TokenCipher(base64_encode(random_bytes(32)));
        $gateway = $this->createMock(OAuthGatewayInterface::class);
        $gateway->method('configured')->willReturn(true);
        $gateway->method('authorizationUrl')->willReturn('https://provider.example/authorize');
        $gateway->expects($this->once())->method('exchange')->with('x', 'private-code', $this->isType('string'))->willReturn(new OAuthTokens('private-access', 'private-refresh', new DateTimeImmutable('+1 hour'), 'remote-owner'));
        $bluesky = $this->createMock(BlueskyGatewayInterface::class);
        $bus = self::getContainer()->get(MessageBusInterface::class);
        $handler = new ConnectionHandler($this->entityManager, $gateway, $cipher, $bus, $bluesky);
        $start = $handler->authorize($user, 'x');
        foreach ([null, 'forged-state'] as $state) {
            try {
                $handler->callback('x', $start->state, $state, 'private-code');
                $this->fail('Invalid state accepted');
            } catch (BadRequestHttpException $error) {
                $this->assertSame('Invalid authorization state.', $error->getMessage());
            }
        }
        $raindrop = $handler->authorize($user, 'raindrop');

        try {
            $handler->callback('raindrop', $raindrop->state, null, 'unbound-code');
            $this->fail('Missing response state accepted');
        } catch (BadRequestHttpException $error) {
            $this->assertSame('Invalid authorization state.', $error->getMessage());
        }
        $handler->callback('x', $start->state, $start->state, 'private-code');
        $attempt = $this->entityManager->find(OAuthAttempt::class, $start->attemptId);
        $this->assertInstanceOf(OAuthAttempt::class, $attempt);
        $this->assertStringNotContainsString('private-code', $attempt->getEncryptedCode() ?? '');
        $worker = new ExchangeOAuthCodeMessageHandler($this->entityManager, $gateway, $cipher, $bluesky);
        $worker(new ExchangeOAuthCodeMessage($start->attemptId));
        $worker(new ExchangeOAuthCodeMessage($start->attemptId));
        $this->assertSame('connected', $attempt->getStatus());
        $this->assertNull($attempt->getEncryptedCode());
        $account = $this->entityManager->getRepository(ExternalAccount::class)->findOneBy(['owner' => $user]);
        $this->assertInstanceOf(ExternalAccount::class, $account);
        $output = json_encode($handler->list($user), JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('private-access', $output);
        $this->assertStringNotContainsString('private-refresh', $output);
        $this->expectException(NotFoundHttpException::class);
        $handler->account($other, $account->getId()->toRfc4122());
    }

    public function testCipherRejectsTamperingAndWrongKey(): void
    {
        $cipher = new TokenCipher(base64_encode(random_bytes(32)));
        $encrypted = $cipher->encrypt('private-token');
        $this->assertSame('private-token', $cipher->decrypt($encrypted));
        $this->expectException(UnrecoverableMessageHandlingException::class);
        new TokenCipher(base64_encode(random_bytes(32)))->decrypt($encrypted);
    }
}
