<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\AI;

use App\Infrastructure\AI\OllamaDigestIntelligence;
use App\Infrastructure\External\BoundedJsonClient;

use const JSON_THROW_ON_ERROR;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class OllamaDigestIntelligenceTest extends TestCase
{
    public function testLocalProviderUsesBoundedStructuredGeneration(): void
    {
        $http = new MockHttpClient(function (string $method, string $url, array $options): MockResponse {
            $this->assertSame('POST', $method);
            $this->assertSame('http://localhost:11434/api/generate', $url);
            $this->assertIsString($options['body']);
            $body = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            $this->assertIsArray($body);
            $this->assertIsArray($body['options']);
            $this->assertIsString($body['system']);
            $this->assertFalse($body['stream']);
            $this->assertSame(1600, $body['options']['num_predict']);
            $this->assertStringContainsString('signalist.digest.v1', $body['system']);

            return new MockResponse('{"response":"{\"summaries\":[\"A short summary.\"]}"}');
        });
        $ai = new OllamaDigestIntelligence(new BoundedJsonClient($http), 'http://localhost:11434', 'fixture-model');
        $this->assertTrue($ai->configured());
        $this->assertSame(['A short summary.'], $ai->summarize([['title' => 'Title', 'text' => 'Source facts.']]));
    }

    public function testExternalHostsAreNotAcceptedAsLocalInference(): void
    {
        $ai = new OllamaDigestIntelligence(new BoundedJsonClient(new MockHttpClient()), 'https://external.example', 'model');
        $this->assertFalse($ai->configured());
        $this->expectException(RuntimeException::class);
        $ai->summarize([]);
    }

    public function testIncompleteResponsesAreRejected(): void
    {
        $ai = new OllamaDigestIntelligence(new BoundedJsonClient(new MockHttpClient(new MockResponse('{"response":"{\"summaries\":[]}"}'))), 'http://localhost:11434', 'model');
        $this->expectException(RuntimeException::class);
        $ai->summarize([['title' => 'Title', 'text' => 'Facts']]);
    }
}
