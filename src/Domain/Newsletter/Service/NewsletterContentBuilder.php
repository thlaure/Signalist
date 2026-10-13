<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\Service;

use App\Domain\AI\Service\ArticleTextCleaner;
use App\Entity\Article;

use function array_slice;
use function count;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

use function in_array;

use const PHP_URL_SCHEME;
use const PREG_SPLIT_NO_EMPTY;

final readonly class NewsletterContentBuilder
{
    public function __construct(private ArticleTextCleaner $cleaner)
    {
    }

    /** @param list<Article> $articles */
    public function build(string $title, array $articles, int $readingMinutes): string
    {
        $budget = $readingMinutes * 200;
        $sections = [];
        $used = $this->wordCount($title);
        foreach ($articles as $article) {
            if (!in_array(parse_url($article->getUrl(), PHP_URL_SCHEME), ['https', 'http'], true)) {
                continue;
            }
            $summary = $this->cleaner->clean($article->getAiSummary() ?? $article->getSummary() ?? $article->getContent() ?? '');
            $summary = implode(' ', array_slice(preg_split('/\s+/u', $summary, -1, PREG_SPLIT_NO_EMPTY) ?: [], 0, 120));
            $category = $article->getFeed()->getCategory()->getName();
            $cost = $this->wordCount($article->getTitle().' '.$summary) + (isset($sections[$category]) ? 0 : $this->wordCount($category));

            if ($used + $cost > $budget) {
                continue;
            }
            $used += $cost;
            $sections[$category][] = '<article><h3><a href="'.$this->escape($article->getUrl()).'">'.$this->escape($article->getTitle()).'</a></h3><p>'.$this->escape($summary).'</p></article>';
        }
        $body = '<h1>'.$this->escape($title).'</h1>';
        foreach ($sections as $category => $items) {
            $body .= '<section><h2>'.$this->escape((string) $category).'</h2>'.implode('', $items).'</section>';
        }

        return '<!doctype html><html lang="en"><head><meta charset="utf-8"><title>'.$this->escape($title).'</title></head><body style="font-family:Arial,sans-serif;max-width:640px;margin:auto">'.$body.'</body></html>';
    }

    public function wordCount(string $text): int
    {
        return count(preg_split('/\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
