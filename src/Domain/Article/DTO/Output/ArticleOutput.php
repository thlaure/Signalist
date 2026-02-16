<?php

declare(strict_types=1);

namespace App\Domain\Article\DTO\Output;

use App\Entity\Article;
use DateTimeInterface;

final readonly class ArticleOutput
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

    public static function fromEntity(Article $article): self
    {
        $feed = $article->getFeed();
        $category = $feed->getCategory();

        return new self(
            id: $article->getId()->toRfc4122(),
            title: $article->getTitle(),
            url: $article->getUrl(),
            summary: $article->getSummary(),
            content: $article->getContent(),
            author: $article->getAuthor(),
            imageUrl: $article->getImageUrl(),
            isRead: $article->isRead(),
            publishedAt: $article->getPublishedAt()?->format(DateTimeInterface::ATOM),
            createdAt: $article->getCreatedAt()->format(DateTimeInterface::ATOM),
            feedId: $feed->getId()->toRfc4122(),
            feedTitle: $feed->getTitle(),
            categoryId: $category->getId()->toRfc4122(),
            categoryName: $category->getName(),
        );
    }
}
