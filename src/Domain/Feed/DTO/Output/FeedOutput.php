<?php

declare(strict_types=1);

namespace App\Domain\Feed\DTO\Output;

use App\Entity\Feed;
use DateTimeInterface;

final readonly class FeedOutput
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $status,
        public ?string $lastError,
        public ?string $lastFetchedAt,
        public string $categoryId,
        public string $categoryName,
        public string $createdAt,
        public string $updatedAt,
    ) {
    }

    public static function fromEntity(Feed $feed): self
    {
        return new self(
            id: $feed->getId()->toRfc4122(),
            title: $feed->getTitle(),
            url: $feed->getUrl(),
            status: $feed->getStatus(),
            lastError: $feed->getLastError(),
            lastFetchedAt: $feed->getLastFetchedAt()?->format(DateTimeInterface::ATOM),
            categoryId: $feed->getCategory()->getId()->toRfc4122(),
            categoryName: $feed->getCategory()->getName(),
            createdAt: $feed->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $feed->getUpdatedAt()->format(DateTimeInterface::ATOM),
        );
    }
}
