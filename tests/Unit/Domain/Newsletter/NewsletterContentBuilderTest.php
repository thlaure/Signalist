<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Newsletter;

use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Newsletter\Service\NewsletterContentBuilder;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use PHPUnit\Framework\TestCase;

final class NewsletterContentBuilderTest extends TestCase
{
    private function article(string $title, string $url): Article
    {
        $category = new Category()->setName('Tech & news');
        $feed = new Feed()->setCategory($category);

        return new Article()->setTitle($title)->setUrl($url)->setFeed($feed)->setSummary(str_repeat('word ', 120));
    }

    public function testBudgetEscapingAndSafeSourceLinks(): void
    {
        $builder = new NewsletterContentBuilder(new ArticleTextCleaner());
        $html = $builder->build('Daily <script>', [$this->article('<img onerror=alert(1)>', 'https://example.com/a?x=1&y=2'), $this->article('Second article', 'https://example.com/b'), $this->article('Unsafe', 'javascript:alert(1)')], 1);
        $this->assertStringContainsString('Tech &amp; news', $html);
        $this->assertStringContainsString('href="https://example.com/a?x=1&amp;y=2"', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<img', $html);
        $this->assertStringNotContainsString('Second article', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertLessThanOrEqual(200, $builder->wordCount(strip_tags($html)));
    }
}
