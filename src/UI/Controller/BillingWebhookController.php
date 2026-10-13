<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Infrastructure\Billing\BillingWebhookReceiver;

use function strlen;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

final readonly class BillingWebhookController
{
    public function __construct(private BillingWebhookReceiver $receiver)
    {
    }

    #[Route('/api/v1/billing/webhook', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $body = $request->getContent();

        if (strlen($body) > 262144) {
            throw new HttpException(413, 'Billing event exceeds size limit.');
        }
        $this->receiver->receive($body, $request->headers->get('Stripe-Signature', ''));

        return new Response(status: 204);
    }
}
