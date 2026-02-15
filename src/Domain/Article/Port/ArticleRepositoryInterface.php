<?php

declare(strict_types=1);

namespace App\Domain\Article\Port;

use App\Entity\Article;

interface ArticleRepositoryInterface
{
    public function save(Article $article): void;

    public function find(string $id): ?Article;

    /**
     * @param array{feedId?: string, categoryId?: string, isRead?: bool, ownerId?: string} $filters
     *
     * @return Article[]
     */
    public function findAll(array $filters = []): array;

    /**
     * @return Article[]
     */
    public function findByFeed(string $feedId): array;

    /**
     * @return Article[]
     */
    public function findUnreadByOwner(string $ownerId): array;
}
