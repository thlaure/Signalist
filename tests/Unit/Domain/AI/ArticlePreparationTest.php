<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\AI;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use PHPUnit\Framework\TestCase;

final class ArticlePreparationTest extends TestCase
{
    public function testRemovesExecutableAndNavigationContentAndPreservesUnicode(): void
    {
        $text = new ArticleTextCleaner()->clean('<nav>Menu</nav><script>secret()</script><style>.hidden{}</style><h1>Économie</h1><p>First &amp; second.</p><p>Third.</p>');
        $this->assertSame('Économie First & second. Third.', $text);
    }

    public function testChunksStayBoundedWithoutSplittingUnicodeCharacters(): void
    {
        $cleaner = new ArticleTextCleaner();
        $text = trim(str_repeat('économie croissance ', 100));
        $chunks = $cleaner->chunks($text, 100);
        $this->assertSame($text, implode(' ', $chunks));
        foreach ($chunks as $chunk) {
            $this->assertLessThanOrEqual(100, mb_strlen($chunk));
            $this->assertTrue(mb_check_encoding($chunk, 'UTF-8'));
        }
    }

    public function testRedactsKnownNamesEmailPhoneAndIpAddresses(): void
    {
        $result = new ArticleAnonymizer()->anonymize('Jane Doe: jane@example.com, +33 6 12 34 56 78, 192.168.1.10, 2001:db8::1.', ['Jane Doe']);
        $this->assertStringContainsString('[PERSON]', $result);
        $this->assertStringContainsString('[EMAIL]', $result);
        $this->assertStringContainsString('[PHONE]', $result);
        $this->assertStringNotContainsString('192.168.1.10', $result);
        $this->assertStringNotContainsString('2001:db8::1', $result);
    }
}
