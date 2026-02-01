<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use App\Infrastructure\ApiPlatform\State\ArticleStateProcessor;
use App\Infrastructure\ApiPlatform\State\ArticleStateProvider;

#[ApiResource(
    shortName: 'Article',
    operations: [
        new GetCollection(
            uriTemplate: '/articles',
            provider: ArticleStateProvider::class,
        ),
        new Get(
            uriTemplate: '/articles/{id}',
            provider: ArticleStateProvider::class,
        ),
        new Patch(
            uriTemplate: '/articles/{id}/read',
            name: 'mark_read',
            provider: ArticleStateProvider::class,
            processor: ArticleStateProcessor::class,
        ),
        new Patch(
            uriTemplate: '/articles/{id}/unread',
            name: 'mark_unread',
            provider: ArticleStateProvider::class,
            processor: ArticleStateProcessor::class,
        ),
    ],
)]
final readonly class ArticleResource
{
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public ?string $summary,
        public ?string $content,
        public ?string $author,
        public ?string $imageUrl,
        public bool $isRead,
        public ?string $publishedAt,
        public string $createdAt,
        public string $feedId,
        public string $feedTitle,
        public string $categoryId,
        public string $categoryName,
    ) {
    }
}
