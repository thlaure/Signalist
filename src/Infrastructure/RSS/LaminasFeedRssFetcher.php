<?php

declare(strict_types=1);

namespace App\Infrastructure\RSS;

use App\Domain\Feed\Port\RssFetcherInterface;
use DateTimeImmutable;
use DateTimeInterface;

use const ENT_HTML5;
use const ENT_QUOTES;

use Exception;

use function is_array;
use function is_string;
use function iterator_to_array;

use Laminas\Feed\Reader\Entry\EntryInterface;
use Laminas\Feed\Reader\Reader;

use function reset;

use RuntimeException;

use function sprintf;

/**
 * RSS fetcher implementation using Laminas Feed.
 */
final readonly class LaminasFeedRssFetcher implements RssFetcherInterface
{
    public function fetch(string $url): RssFetchResult
    {
        try {
            $feed = Reader::import($url);
        } catch (Exception $e) {
            throw new RuntimeException(sprintf('Failed to fetch RSS feed from "%s": %s', $url, $e->getMessage()), $e->getCode(), previous: $e);
        }

        $feedTitle = $feed->getTitle() ?? $url;
        $articles = [];

        /** @var EntryInterface $entry */
        foreach ($feed as $entry) {
            $guid = $entry->getId() ?? $entry->getLink() ?? '';

            if ($guid === '') {
                continue;
            }

            $link = $entry->getLink();

            if ($link === null) {
                continue;
            }

            if ($link === '') {
                continue;
            }

            $publishedAt = null;
            $dateModified = $entry->getDateModified();

            if ($dateModified instanceof DateTimeInterface) {
                $publishedAt = DateTimeImmutable::createFromInterface($dateModified);
            }

            $articles[] = new RssFetchedArticle(
                guid: $guid,
                title: $entry->getTitle() ?? 'Untitled',
                url: $link,
                summary: $this->cleanText($entry->getDescription()),
                content: $entry->getContent(),
                author: $this->extractAuthor($entry),
                imageUrl: null,
                publishedAt: $publishedAt,
            );
        }

        return new RssFetchResult(
            feedTitle: $feedTitle,
            articles: $articles,
        );
    }

    private function cleanText(?string $text): ?string
    {
        if ($text === null) {
            return null;
        }

        $stripped = strip_tags($text);
        $decoded = html_entity_decode($stripped, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $decoded));

        if ($normalized === '' || mb_strlen($normalized) < 30) {
            return null;
        }

        return $normalized;
    }

    private function extractAuthor(EntryInterface $entry): ?string
    {
        $authors = $entry->getAuthors();

        if ($authors === null) {
            return null;
        }

        // Convert iterable to array to safely access first element
        $authorsArray = iterator_to_array($authors);

        if ($authorsArray === []) {
            return null;
        }

        $firstAuthor = reset($authorsArray);

        if (!is_array($firstAuthor)) {
            return null;
        }

        $name = $firstAuthor['name'] ?? null;

        return is_string($name) ? $name : null;
    }
}
