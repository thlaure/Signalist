<?php

declare(strict_types=1);

namespace App\Tests\Integration\Billing;

use App\Domain\Billing\DTO\BillingSnapshot;
use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Message\ReconcileBillingEventMessage;
use App\Domain\Billing\MessageHandler\ReconcileBillingEventMessageHandler;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\UsageBudget;
use App\Entity\Subscription;
use App\Entity\User;
use App\Infrastructure\Billing\BillingWebhookReceiver;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

use const JSON_THROW_ON_ERROR;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

final class BillingWorkflowTest extends DatabaseTestCase
{
    public function testSignedWebhookReconcilesCurrentStateAndReplayDoesNotDuplicateWork(): void
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $subscription = new Subscription($user);
        $subscription->setCustomerId('cus_fixture');
        $this->entityManager->persist($user);
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
        $body = json_encode(['id' => 'evt_fixture', 'object' => 'event', 'type' => 'customer.subscription.updated', 'data' => ['object' => ['object' => 'subscription', 'customer' => 'cus_fixture']]], JSON_THROW_ON_ERROR);
        $timestamp = time();
        $signature = 't='.$timestamp.',v1='.hash_hmac('sha256', $timestamp.'.'.$body, 'fixture-secret');
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
        $receiver = new BillingWebhookReceiver($this->entityManager, $bus, 'fixture-secret');
        $receiver->receive($body, $signature);
        $gateway = $this->createMock(BillingGatewayInterface::class);
        $gateway->expects($this->once())->method('currentSubscription')->with('cus_fixture')->willReturn(new BillingSnapshot('active', 'team', new DateTimeImmutable('+1 month')));
        $handler = new ReconcileBillingEventMessageHandler($this->entityManager, $gateway);
        $handler(new ReconcileBillingEventMessage('evt_fixture'));
        $handler(new ReconcileBillingEventMessage('evt_fixture'));
        $receiver->receive($body, $signature);
        $this->assertSame('team', $user->getPlan());
        $this->assertSame('active', $subscription->getStatus());
    }

    public function testForgedWebhookCannotUpdateEntitlements(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->never())->method('dispatch');
        $this->expectException(BadRequestHttpException::class);
        new BillingWebhookReceiver($this->entityManager, $bus, 'fixture-secret')->receive('{"id":"fake"}', 't='.time().',v1=forged');
    }

    public function testAtomicBudgetCannotExceedConfiguredLimit(): void
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $user->setPlan('team', new DateTimeImmutable('+1 month'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $plans = PlanCatalog::DEFAULTS;
        $plans['team']['monthlyAiCalls'] = 3;
        $budget = new UsageBudget($this->entityManager->getConnection(), new PlanCatalog($plans));
        $budget->aiCalls($user, 2);
        $budget->aiCalls($user, 1);
        $this->expectException(PlanLimitReachedException::class);
        $budget->aiCalls($user, 1);
    }

    public function testFreeDailyArticleLimitAndExpiredEntitlements(): void
    {
        $user = new User()->setEmail('owner@example.com')->setPassword('hash');
        $user->setPlan('team', new DateTimeImmutable('-1 minute'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->assertSame('free', $user->getPlan());
        $plans = PlanCatalog::DEFAULTS;
        $plans['free']['dailyArticles'] = 2;
        $budget = new UsageBudget($this->entityManager->getConnection(), new PlanCatalog($plans));
        $this->assertTrue($budget->article($user));
        $this->assertTrue($budget->article($user));
        $this->assertFalse($budget->article($user));
    }
}
