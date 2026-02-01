<?php

declare(strict_types=1);

namespace App\Domain\Feed\Port;

use App\Infrastructure\RSS\RssFetchResult;
use RuntimeException;

interface RssFetcherInterface
{
    /**
     * Fetches and parses an RSS feed from the given URL.
     *
     * @throws RuntimeException When the feed cannot be fetched or parsed
     */
    public function fetch(string $url): RssFetchResult;
}
