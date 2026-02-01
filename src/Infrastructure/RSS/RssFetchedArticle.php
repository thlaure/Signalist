<?php

declare(strict_types=1);

namespace App\Infrastructure\RSS;

use DateTimeImmutable;

/**
 * Represents a single article fetched from an RSS feed.
 */
final readonly class RssFetchedArticle
{
    public function __construct(
        public string $guid,
        public string $title,
        public string $url,
        public ?string $summary = null,
        public ?string $content = null,
        public ?string $author = null,
        public ?string $imageUrl = null,
        public ?DateTimeImmutable $publishedAt = null,
    ) {
    }
}
