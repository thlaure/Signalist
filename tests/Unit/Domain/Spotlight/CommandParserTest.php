<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Spotlight;

use App\Domain\Spotlight\Service\CommandParser;
use PHPUnit\Framework\TestCase;

final class CommandParserTest extends TestCase
{
    public function testEnglishAndFrenchMutationsAndSafeSearchFallback(): void
    {
        $parser = new CommandParser();
        $this->assertSame(['intent' => 'create_category', 'value' => 'Économie'], $parser->parse('créer catégorie Économie'));
        $this->assertSame(['intent' => 'add_feed', 'value' => 'https://example.com/rss', 'category' => 'Tech'], $parser->parse('add feed https://example.com/rss in Tech'));
        $this->assertSame('search', $parser->parse('delete everything')['intent']);
        $this->assertSame('search', $parser->parse('add feed file:///etc/passwd in Tech')['intent']);
    }
}
