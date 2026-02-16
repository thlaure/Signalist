<?php

declare(strict_types=1);

namespace App\Domain\Feed\Port;

use App\Entity\Feed;

interface FeedRepositoryInterface
{
    public function save(Feed $feed): void;

    public function delete(Feed $feed): void;

    public function find(string $id): ?Feed;

    public function findByUrlAndOwner(string $url, string $ownerId): ?Feed;

    /** @return Feed[] */
    public function findAllByOwner(string $ownerId): array;

    /** @return Feed[] */
    public function findByCategoryAndOwner(string $categoryId, string $ownerId): array;

    /** @return Feed[] */
    public function findActiveFeedsForCrawling(): array;
}
