<?php

declare(strict_types=1);

namespace App\Tests\Integration\Billing;

use App\Domain\Billing\Handler\BillingHandler;
use App\Domain\Billing\Message\CreateBillingLinkMessage;
use App\Domain\Billing\Message\SyncBillingSeatsMessage;
use App\Domain\Billing\MessageHandler\CreateBillingLinkMessageHandler;
use App\Domain\Billing\MessageHandler\SyncBillingSeatsMessageHandler;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\SeatSyncService;
use App\Domain\Organization\Handler\OrganizationHandler;
use App\Domain\Organization\Service\OrganizationPolicy;
use App\Entity\SeatSyncRequest;
use App\Entity\Subscription;
use App\Entity\User;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

final class BillingLinksAndSeatsTest extends DatabaseTestCase
{
    public function testCheckoutAndPortalAreScopedAndExistingSubscriptionsCannotCheckoutAgain(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        $gateway = $this->createMock(BillingGatewayInterface::class);
        $gateway->method('configured')->willReturn(true);
        $gateway->expects($this->once())->method('customer')->willReturn('cus_fixture');
        $gateway->expects($this->exactly(2))->method('link')->willReturn('https://checkout.stripe.com/fixture');
        $handler = new BillingHandler($this->entityManager, self::getContainer()->get(OrganizationHandler::class), $gateway, new PlanCatalog(), self::getContainer()->get(MessageBusInterface::class));
        $this->assertSame('free', $handler->status($owner, null)['plan']);
        $link = $handler->createLink($owner, 'checkout', null);
        $worker = new CreateBillingLinkMessageHandler($this->entityManager, $gateway, new OrganizationPolicy());
        $message = new CreateBillingLinkMessage($link->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $this->assertSame('complete', $handler->link($owner, $link->getId()->toRfc4122())['status']);

        try {
            $handler->link($other, $link->getId()->toRfc4122());
            $this->fail('Foreign link exposed');
        } catch (NotFoundHttpException $error) {
            $this->assertSame(404, $error->getStatusCode());
        }
        $link->getSubscription()->apply('active', 'team', new DateTimeImmutable('+1 month'));
        $this->entityManager->flush();
        $portal = $handler->createLink($owner, 'portal', null);
        $worker(new CreateBillingLinkMessage($portal->getId()->toRfc4122()));
        $this->assertSame('team', $handler->status($owner, null)['plan']);
        $this->expectException(ConflictHttpException::class);
        $handler->createLink($owner, 'checkout', null);
    }

    public function testSeatOutboxUsesCurrentMembershipAndDoesNotRepeatCompletedWork(): void
    {
        $owner = $this->user('owner@example.com');
        $organizations = self::getContainer()->get(OrganizationHandler::class);
        $organization = $organizations->create($owner, 'Team');
        $subscription = new Subscription(null, $organization);
        $subscription->setCustomerId('cus_team');
        $this->entityManager->persist($subscription);
        $this->entityManager->flush();
        new SeatSyncService($this->entityManager)->queue($organization);
        $this->entityManager->flush();
        $request = $this->entityManager->getRepository(SeatSyncRequest::class)->findOneBy(['subscription' => $subscription]);
        $this->assertInstanceOf(SeatSyncRequest::class, $request);
        $gateway = $this->createMock(BillingGatewayInterface::class);
        $gateway->expects($this->once())->method('updateSeats')->with('cus_team', 1, $request->getId()->toRfc4122());
        $worker = new SyncBillingSeatsMessageHandler($this->entityManager, $gateway);
        $message = new SyncBillingSeatsMessage($request->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $this->assertTrue($request->isProcessed());
    }

    private function user(string $email): User
    {
        $user = new User()->setEmail($email)->setPassword('hash');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}
