<?php

declare(strict_types=1);

namespace App\Domain\Feed\Port;

use App\Entity\Feed;

interface FeedRepositoryInterface
{
    public function save(Feed $feed): void;

    public function delete(Feed $feed): void;

    public function find(string $id): ?Feed;

    public function findByUrl(string $url): ?Feed;

    /** @return Feed[] */
    public function findAll(): array;

    /** @return Feed[] */
    public function findByCategory(string $categoryId): array;

    /** @return Feed[] */
    public function findActiveFeedsForCrawling(): array;
}
