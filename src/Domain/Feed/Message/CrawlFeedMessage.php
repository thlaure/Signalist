<?php

declare(strict_types=1);

namespace App\Domain\Feed\Message;

/**
 * Message dispatched to trigger async RSS feed crawling.
 */
final readonly class CrawlFeedMessage
{
    public function __construct(
        public string $feedId,
    ) {
    }
}
