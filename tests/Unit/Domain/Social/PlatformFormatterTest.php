<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Social;

use App\Domain\Social\Service\PlatformFormatter;

use function count;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class PlatformFormatterTest extends TestCase
{
    public function testThreadPreservesUrlsAndStaysWithinWeightedLimit(): void
    {
        $formatter = new PlatformFormatter();
        $url = 'https://example.com/'.str_repeat('source', 50);
        $posts = $formatter->format('x', str_repeat('Useful source 😀 ', 50).$url);
        $this->assertGreaterThan(1, count($posts));
        foreach ($posts as $post) {
            $this->assertLessThanOrEqual(280, $formatter->length('x', $post));
        }
        $this->assertStringContainsString($url, implode(' ', $posts));
        $this->assertStringContainsString('1/'.count($posts), $posts[0]);
    }

    public function testBlueskyCountsGraphemesAndRejectsOversizeContent(): void
    {
        $formatter = new PlatformFormatter();
        $this->assertCount(1, $formatter->format('bluesky', str_repeat('👨‍👩‍👧‍👦', 50)));
        $this->expectException(InvalidArgumentException::class);
        $formatter->format('bluesky', str_repeat('a', 301));
    }

    public function testInstagramCaptionHasNoInventedHashtags(): void
    {
        $this->assertSame(['Source facts'], new PlatformFormatter()->format('instagram', '<b>Source facts</b>'));
    }
}
