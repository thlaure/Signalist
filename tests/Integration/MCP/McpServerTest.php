<?php

declare(strict_types=1);

namespace App\Tests\Integration\MCP;

use App\Entity\User;
use App\Infrastructure\MCP\ArticleTools;
use App\Infrastructure\MCP\McpServerFactory;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

use const JSON_THROW_ON_ERROR;

use Mcp\Server\Transport\StreamableHttpTransport;
use Nyholm\Psr7\ServerRequest;
use stdClass;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

final class McpServerTest extends DatabaseTestCase
{
    public function testHandshakeListsOnlyReadOnlyToolsAndSessionIsPrincipalScoped(): void
    {
        $user = new User()->setEmail('mcp@example.com')->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $other = new User()->setEmail('other@example.com')->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $this->entityManager->persist($user);
        $this->entityManager->persist($other);
        $this->entityManager->flush();
        $storage = self::getContainer()->get(TokenStorageInterface::class);
        $storage->setToken(new UsernamePasswordToken($user, 'api', $user->getRoles()));
        $factory = new McpServerFactory(self::getContainer()->get(ArticleTools::class), sys_get_temp_dir().'/signalist-mcp-tests');
        $server = $factory->create($user);
        $response = $server->run(new StreamableHttpTransport(new ServerRequest('POST', 'http://localhost/mcp/', [
            'Content-Type' => 'application/json', 'Accept' => 'application/json, text/event-stream',
        ], json_encode(['jsonrpc' => '2.0', 'id' => 1, 'method' => 'initialize', 'params' => ['protocolVersion' => '2025-11-25', 'capabilities' => new stdClass(), 'clientInfo' => ['name' => 'test', 'version' => '1.0']]], JSON_THROW_ON_ERROR))));
        $this->assertSame(200, $response->getStatusCode());
        $session = $response->getHeaderLine('Mcp-Session-Id');
        $this->assertNotSame('', $session);
        $listRequest = new ServerRequest('POST', 'http://localhost/mcp/', ['Content-Type' => 'application/json', 'Accept' => 'application/json, text/event-stream', 'Mcp-Session-Id' => $session, 'MCP-Protocol-Version' => '2025-11-25'], json_encode(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'], JSON_THROW_ON_ERROR));
        $result = $factory->create($user)->run(new StreamableHttpTransport($listRequest));
        $this->assertSame(200, $result->getStatusCode());
        $body = (string) $result->getBody();
        $this->assertStringContainsString('search_articles', $body);
        $this->assertStringContainsString('get_feed_summary', $body);
        $this->assertStringContainsString('list_categories', $body);
        $this->assertStringContainsString('get_article', $body);
        $this->assertStringContainsString('"readOnlyHint":true', $body);
        $crossUser = $factory->create($other)->run(new StreamableHttpTransport($listRequest));
        $this->assertSame(404, $crossUser->getStatusCode());
    }
}
