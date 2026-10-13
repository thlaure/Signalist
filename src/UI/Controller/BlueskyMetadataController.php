<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Connection\Port\BlueskyGatewayInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

final readonly class BlueskyMetadataController
{
    public function __construct(private BlueskyGatewayInterface $gateway)
    {
    }

    #[Route('/oauth/bluesky/{document}.json', requirements: ['document' => 'client-metadata|jwks'], methods: ['GET'])]
    public function __invoke(string $document): JsonResponse
    {
        if (!$this->gateway->configured()) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->gateway->metadata('jwks' === $document), headers: ['Cache-Control' => 'public, max-age=300']);
    }
}
