<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Domain\Connection\Handler\ConnectionHandler;

use function is_string;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class OAuthCallbackController
{
    public function __construct(private ConnectionHandler $handler, private string $frontendUrl)
    {
    }

    #[Route('/api/v1/connections/{provider}/callback', requirements: ['provider' => 'raindrop|linkedin|x|bluesky'], methods: ['GET'])]
    public function __invoke(string $provider, Request $request): RedirectResponse
    {
        $queryState = $request->query->get('state');
        $code = $request->query->get('code');
        $issuer = $request->query->get('iss');
        $this->handler->callback($provider, $request->cookies->getString('signalist_oauth_'.$provider), is_string($queryState) ? $queryState : null, is_string($code) && !$request->query->has('error') ? $code : null, is_string($issuer) ? $issuer : null);
        $response = new RedirectResponse(rtrim($this->frontendUrl, '/').'/connections');
        $response->headers->clearCookie('signalist_oauth_'.$provider, '/api/v1/connections/'.$provider.'/callback');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        return $response;
    }
}
