<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\DTO\Output;

use App\Entity\Bookmark;
use DateTimeInterface;

final readonly class BookmarkOutput
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

    public static function fromEntity(Bookmark $bookmark): self
    {
        $article = $bookmark->getArticle();
        $feed = $article->getFeed();
        $category = $feed->getCategory();

        return new self(
            id: $bookmark->getId()->toRfc4122(),
            notes: $bookmark->getNotes(),
            createdAt: $bookmark->getCreatedAt()->format(DateTimeInterface::ATOM),
            articleId: $article->getId()->toRfc4122(),
            articleTitle: $article->getTitle(),
            articleUrl: $article->getUrl(),
            feedId: $feed->getId()->toRfc4122(),
            feedTitle: $feed->getTitle(),
            categoryId: $category->getId()->toRfc4122(),
            categoryName: $category->getName(),
        );
    }
}
