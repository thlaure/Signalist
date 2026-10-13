<?php

declare(strict_types=1);

namespace App\Domain\Billing\MessageHandler;

use App\Domain\Billing\Message\CreateBillingLinkMessage;
use App\Domain\Billing\Port\BillingGatewayInterface;
use App\Domain\Organization\Service\OrganizationPolicy;
use App\Entity\BillingLink;
use App\Entity\Organization;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

use function in_array;

use const PHP_URL_HOST;
use const PHP_URL_SCHEME;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class CreateBillingLinkMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private BillingGatewayInterface $gateway, private OrganizationPolicy $policy)
    {
    }

    public function __invoke(CreateBillingLinkMessage $message): void
    {
        $link = $this->entityManager->find(BillingLink::class, Uuid::fromString($message->linkId));

        if (!$link instanceof BillingLink || 'pending' !== $link->getStatus()) {
            return;
        }
        $actor = $link->getRequestedBy();
        $subscription = $link->getSubscription();
        $organization = $subscription->getOrganization();

        if ($actor->isDeleted() || $actor->isProcessingRestricted() || $link->getCreatedAt() < new DateTimeImmutable('-30 minutes') || ($organization instanceof Organization && !$this->policy->allows($actor, $organization, 'manage'))) {
            $link->fail();
            $this->entityManager->flush();

            return;
        }

        try {
            if (null === $subscription->getCustomerId()) {
                $subscription->setCustomerId($this->gateway->customer($subscription->getId()->toRfc4122()));
                $this->entityManager->flush();
            }
            $url = $this->gateway->link($link->getKind(), $subscription->getCustomerId() ?? '', $organization?->getMembers()->count() ?? 1, $message->linkId);

            if ('https' !== parse_url($url, PHP_URL_SCHEME) || !in_array(parse_url($url, PHP_URL_HOST), ['checkout.stripe.com', 'billing.stripe.com'], true)) {
                throw new UnrecoverableMessageHandlingException('Invalid billing destination.');
            }
            $link->complete($url);
            $this->entityManager->flush();
        } catch (UnrecoverableMessageHandlingException $exception) {
            $link->fail();
            $this->entityManager->flush();

            throw $exception;
        }
    }
}
