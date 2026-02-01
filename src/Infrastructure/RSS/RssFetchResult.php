<?php

declare(strict_types=1);

namespace App\Infrastructure\RSS;

/**
 * Represents the result of fetching an RSS feed.
 */
final readonly class RssFetchResult
{
    /**
     * @param RssFetchedArticle[] $articles
     */
    public function __construct(
        public string $feedTitle,
        public array $articles,
    ) {
    }
}
