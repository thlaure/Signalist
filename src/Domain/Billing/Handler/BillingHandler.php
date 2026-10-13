<?php

declare(strict_types=1);

namespace App\Domain\Billing\Handler;

use App\Domain\Billing\Message\CreateBillingLinkMessage;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Organization\Handler\OrganizationHandler;
use App\Entity\BillingLink;
use App\Entity\Organization;
use App\Entity\Subscription;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Uid\Uuid;

final readonly class BillingHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private OrganizationHandler $organizations, private BillingGatewayInterface $gateway, private PlanCatalog $plans, private MessageBusInterface $bus)
    {
    }

    /** @return array<string, mixed> */
    public function status(User $user, ?string $organizationId): array
    {
        $organization = null === $organizationId ? null : $this->organizations->get($user, $organizationId, 'manage');
        $scope = $organization instanceof Organization ? ['organization' => $organization, 'owner' => null] : ['owner' => $user, 'organization' => null];
        $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy($scope);
        $plan = $organization?->getPlan() ?? $user->getPlan();

        return ['plan' => $plan, 'limits' => $this->plans->limits($plan), 'status' => $subscription?->getStatus() ?? 'inactive', 'configured' => $this->gateway->configured(), 'seats' => $organization?->getMembers()->count() ?? 1];
    }

    public function createLink(User $user, string $kind, ?string $organizationId): BillingLink
    {
        if (!$this->gateway->configured()) {
            throw new ServiceUnavailableHttpException(null, 'Billing setup is pending.');
        }

        if ($user->isProcessingRestricted()) {
            throw new AccessDeniedException('Processing is restricted.');
        }
        $organization = null === $organizationId ? null : $this->organizations->get($user, $organizationId, 'manage');
        $scope = $organization instanceof Organization ? ['organization' => $organization, 'owner' => null] : ['owner' => $user, 'organization' => null];
        $subscription = $this->entityManager->getRepository(Subscription::class)->findOneBy($scope);

        if (!$subscription instanceof Subscription) {
            $subscription = new Subscription($organization instanceof Organization ? null : $user, $organization);
            $this->entityManager->persist($subscription);
        }

        if ('checkout' === $kind && in_array($subscription->getStatus(), ['active', 'trialing'], true)) {
            throw new ConflictHttpException('Manage your existing subscription through the billing portal.');
        }

        if ('portal' === $kind && null === $subscription->getCustomerId()) {
            throw new ConflictHttpException('No billing account exists yet.');
        }
        $link = new BillingLink($subscription, $user, $kind);
        $this->entityManager->persist($link);
        $this->entityManager->flush();
        $this->bus->dispatch(new CreateBillingLinkMessage($link->getId()->toRfc4122()));

        return $link;
    }

    /** @return array{id: string, status: string, url: string|null} */
    public function link(User $user, string $id): array
    {
        $link = Uuid::isValid($id) ? $this->entityManager->find(BillingLink::class, Uuid::fromString($id)) : null;

        if (!$link instanceof BillingLink || !$link->getRequestedBy()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }

        if ($link->getSubscription()->getOrganization() instanceof Organization) {
            $this->organizations->get($user, $link->getSubscription()->getOrganization()->getId()->toRfc4122(), 'manage');
        }

        if ($link->getCreatedAt() < new DateTimeImmutable('-30 minutes')) {
            return ['id' => $id, 'status' => 'expired', 'url' => null];
        }

        return ['id' => $id, 'status' => $link->getStatus(), 'url' => $link->getUrl()];
    }
}
