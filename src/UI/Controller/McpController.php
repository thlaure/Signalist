<?php

declare(strict_types=1);

namespace App\UI\Controller;

use App\Entity\User;
use App\Infrastructure\MCP\McpServerFactory;

use function in_array;
use function is_string;

use Mcp\Server\Transport\Http\Middleware\DnsRebindingProtectionMiddleware;
use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\Factory\Psr17Factory;

use const PHP_URL_HOST;

use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final readonly class McpController
{
    public function __construct(private McpServerFactory $factory, private string $frontendUrl, private string $backendUrl = 'http://localhost:18000')
    {
    }

    #[Route('/mcp/', methods: ['POST', 'GET', 'DELETE', 'OPTIONS'])]
    public function __invoke(Request $request, #[CurrentUser] User $user): Response
    {
        $hosts = ['localhost', '127.0.0.1', '[::1]'];
        foreach ([$this->frontendUrl, $this->backendUrl] as $url) {
            $host = parse_url($url, PHP_URL_HOST);

            if (is_string($host)) {
                $hosts[] = strtolower($host);
            }
        }

        if (!in_array(strtolower($request->getHost()), $hosts, true)) {
            throw new AccessDeniedHttpException('Host not permitted.');
        }
        $origin = $request->headers->get('Origin');

        if (null !== $origin && !in_array($origin, [$request->getSchemeAndHttpHost(), rtrim($this->frontendUrl, '/')], true)) {
            throw new AccessDeniedHttpException('Origin not permitted.');
        }
        $psr17 = new Psr17Factory();
        $bridge = new PsrHttpFactory($psr17, $psr17, $psr17, $psr17);
        $response = $this->factory->create($user)->run(new StreamableHttpTransport($bridge->createRequest($request), $psr17, $psr17, middleware: array_map(static fn ($middleware) => $middleware instanceof DnsRebindingProtectionMiddleware ? new DnsRebindingProtectionMiddleware($hosts, $psr17, $psr17) : $middleware, StreamableHttpTransport::defaultMiddleware()), maxBodyBytes: 262144));
        $result = new HttpFoundationFactory()->createResponse($response);
        $result->headers->set('Cache-Control', 'no-store');

        return $result;
    }
}
