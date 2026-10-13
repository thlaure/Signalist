<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\RSS;

use App\Infrastructure\RSS\LaminasFeedRssFetcher;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class LaminasFeedRssFetcherTest extends TestCase
{
    public function testFetchesRssWithArticleMetadata(): void
    {
        $xml = <<<'XML'
            <rss version="2.0"><channel><title>QA RSS</title><link>https://example.com</link><description>QA feed</description>
            <item><guid>article-1</guid><title>RSS article</title><link>https://example.com/article</link>
            <description><![CDATA[<p>A useful article description with enough words for a summary.</p>]]></description>
            <pubDate>Tue, 13 Oct 2026 10:00:00 GMT</pubDate><author>qa@example.com (QA Author)</author></item>
            </channel></rss>
            XML;
        $client = new MockHttpClient(new MockResponse($xml));
        $result = new LaminasFeedRssFetcher($client)->fetch('https://93.184.216.34/feed');

        self::assertSame('QA RSS', $result->feedTitle);
        self::assertCount(1, $result->articles);
        self::assertSame('article-1', $result->articles[0]->guid);
        self::assertSame('https://example.com/article', $result->articles[0]->url);
        self::assertSame('A useful article description with enough words for a summary.', $result->articles[0]->summary);
        self::assertSame('2026-10-13', $result->articles[0]->publishedAt?->format('Y-m-d'));
    }

    public function testFetchesAtomWithAuthorAndContent(): void
    {
        $xml = <<<'XML'
            <feed xmlns="http://www.w3.org/2005/Atom"><id>urn:qa:feed</id><title>QA Atom</title><updated>2026-10-13T10:00:00Z</updated>
            <entry><id>urn:qa:article</id><title>Atom article</title><updated>2026-10-13T10:00:00Z</updated>
            <link href="https://example.com/atom"/><author><name>QA Author</name></author><content type="html">&lt;p&gt;Article content&lt;/p&gt;</content></entry></feed>
            XML;
        $result = new LaminasFeedRssFetcher(new MockHttpClient(new MockResponse($xml)))->fetch('https://93.184.216.34/feed');

        self::assertSame('QA Atom', $result->feedTitle);
        self::assertSame('QA Author', $result->articles[0]->author);
        self::assertSame('<p>Article content</p>', $result->articles[0]->content);
    }

    public function testRejectsUnsuccessfulHttpResponse(): void
    {
        $fetcher = new LaminasFeedRssFetcher(new MockHttpClient(new MockResponse('private upstream error', ['http_code' => 503])));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch RSS feed.');
        $fetcher->fetch('https://93.184.216.34/feed');
    }

    public function testRejectsOversizedFeed(): void
    {
        $fetcher = new LaminasFeedRssFetcher(new MockHttpClient(new MockResponse(str_repeat('x', 4 * 1024 * 1024 + 1))));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to fetch RSS feed.');
        $fetcher->fetch('https://93.184.216.34/feed');
    }

    public function testRejectsMalformedFeed(): void
    {
        $fetcher = new LaminasFeedRssFetcher(new MockHttpClient(new MockResponse('not XML')));
        $this->expectException(RuntimeException::class);
        $fetcher->fetch('https://93.184.216.34/feed');
    }

    public function testRejectsPrivateNetworkBeforeTransport(): void
    {
        $client = new MockHttpClient(new MockResponse('unused'));

        try {
            new LaminasFeedRssFetcher($client)->fetch('http://127.0.0.1/feed');
            self::fail('Private network source must be rejected.');
        } catch (RuntimeException $error) {
            self::assertSame('Failed to fetch RSS feed.', $error->getMessage());
            self::assertSame(0, $client->getRequestsCount());
        }
    }
}
