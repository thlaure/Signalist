<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Port;

use App\Entity\Bookmark;

interface BookmarkRepositoryInterface
{
    public function save(Bookmark $bookmark): void;

    public function delete(Bookmark $bookmark): void;

    public function find(string $id): ?Bookmark;

    public function findByArticle(string $articleId): ?Bookmark;

    /** @return Bookmark[] */
    public function findAll(): array;
}
