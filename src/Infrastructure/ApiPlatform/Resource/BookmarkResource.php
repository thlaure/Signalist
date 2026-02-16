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
            description: 'List all bookmarks for the authenticated user, ordered by creation date.',
            provider: BookmarkStateProvider::class,
        ),
        new Get(
            uriTemplate: '/bookmarks/{id}',
            description: 'Retrieve a single bookmark with its associated article details.',
            provider: BookmarkStateProvider::class,
        ),
        new Post(
            uriTemplate: '/bookmarks',
            description: 'Bookmark an article. Each article can only be bookmarked once.',
            input: CreateBookmarkInput::class,
            processor: BookmarkStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/bookmarks/{id}',
            description: 'Remove a bookmark. The article itself is not affected.',
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
