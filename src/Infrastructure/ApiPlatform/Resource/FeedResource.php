<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Domain\Feed\DTO\Input\AddFeedInput;
use App\Domain\Feed\DTO\Input\UpdateFeedInput;
use App\Infrastructure\ApiPlatform\State\FeedStateProcessor;
use App\Infrastructure\ApiPlatform\State\FeedStateProvider;

#[ApiResource(
    shortName: 'Feed',
    operations: [
        new GetCollection(
            uriTemplate: '/feeds',
            provider: FeedStateProvider::class,
        ),
        new Get(
            uriTemplate: '/feeds/{id}',
            provider: FeedStateProvider::class,
        ),
        new Post(
            uriTemplate: '/feeds',
            input: AddFeedInput::class,
            processor: FeedStateProcessor::class,
        ),
        new Put(
            uriTemplate: '/feeds/{id}',
            input: UpdateFeedInput::class,
            provider: FeedStateProvider::class,
            processor: FeedStateProcessor::class,
        ),
        new Delete(
            uriTemplate: '/feeds/{id}',
            provider: FeedStateProvider::class,
            processor: FeedStateProcessor::class,
        ),
    ],
)]
final readonly class FeedResource
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
}
