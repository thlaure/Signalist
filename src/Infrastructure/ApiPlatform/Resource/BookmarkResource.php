<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Domain\Bookmark\DTO\Input\CreateBookmarkInput;
use App\Infrastructure\ApiPlatform\State\BookmarkStateProcessor;
use App\Infrastructure\ApiPlatform\State\BookmarkStateProvider;

#[ApiResource(
    shortName: 'Bookmark',
    operations: [
        new GetCollection(
            uriTemplate: '/bookmarks',
            provider: BookmarkStateProvider::class,
        ),
        new Get(
            uriTemplate: '/bookmarks/{id}',
            provider: BookmarkStateProvider::class,
        ),
        new Post(
            uriTemplate: '/bookmarks',
            input: CreateBookmarkInput::class,
            processor: BookmarkStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/bookmarks/{id}',
            provider: BookmarkStateProvider::class,
            processor: BookmarkStateProcessor::class,
        ),
    ],
)]
final readonly class BookmarkResource
{
    public function __construct(
        public string $id,
        public ?string $notes,
        public string $createdAt,
        public string $articleId,
        public string $articleTitle,
        public string $articleUrl,
        public string $feedId,
        public string $feedTitle,
        public string $categoryId,
        public string $categoryName,
    ) {
    }
}
