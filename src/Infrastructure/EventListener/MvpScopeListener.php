<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\Billing\Message\SyncBillingSeatsMessage;

use function in_array;
use function is_array;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Event\WorkerMessageReceivedEvent;

/** Keep legacy data and code, but close the excluded application surface in MVP mode. */
final readonly class MvpScopeListener
{
    public function __construct(private bool $mvpEnabled)
    {
    }

    #[AsEventListener(event: 'kernel.request', priority: 240)]
    public function request(RequestEvent $event): void
    {
        if (!$this->mvpEnabled) {
            return;
        }
        $path = $event->getRequest()->getPathInfo();

        if (preg_match('#^/mcp(?:/|$)#', $path) || (str_starts_with($path, '/api/v1/') && !preg_match('#^/api/v1/(?:auth(?:/|$)|sources(?:/|$)|digests(?:/|$)|user(?:/|$)|billing(?:/|$))#', $path))) {
            throw new NotFoundHttpException('This feature is unavailable in the weekly digest edition.');
        }
        $body = json_decode($event->getRequest()->getContent(), true);

        if (str_starts_with($path, '/api/v1/billing') && ($event->getRequest()->query->has('organizationId') || (is_array($body) && null !== ($body['organizationId'] ?? null)))) {
            throw new NotFoundHttpException();
        }
    }

    #[AsEventListener(event: WorkerMessageReceivedEvent::class)]
    public function received(WorkerMessageReceivedEvent $event): void
    {
        if (!$this->mvpEnabled) {
            return;
        }

        if ($event->getEnvelope()->getMessage() instanceof SyncBillingSeatsMessage) {
            $event->shouldHandle(false);

            return;
        }
        $class = $event->getEnvelope()->getMessage()::class;
        $parts = explode('\\', $class);

        if ('App' === ($parts[0] ?? '') && 'Domain' === ($parts[1] ?? '') && in_array($parts[2] ?? '', ['Newsletter', 'Social', 'Connection', 'Sync', 'Spotlight', 'Search', 'AI', 'Organization'], true)) {
            $event->shouldHandle(false);
        }
    }
}
