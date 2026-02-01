<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Bookmark\Handler\GetBookmarkHandler;
use App\Domain\Bookmark\Handler\ListBookmarksHandler;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Entity\Bookmark;
use App\Infrastructure\ApiPlatform\Resource\BookmarkResource;

use function assert;

use DateTimeInterface;

use function is_string;

/**
 * @implements ProviderInterface<BookmarkResource>
 */
final readonly class BookmarkStateProvider implements ProviderInterface
{
    public function __construct(
        private GetBookmarkHandler $getBookmarkHandler,
        private ListBookmarksHandler $listBookmarksHandler,
    ) {
    }

    /**
     * @return BookmarkResource|array<int, BookmarkResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): BookmarkResource|array
    {
        if ($operation instanceof CollectionOperationInterface) {
            $bookmarks = ($this->listBookmarksHandler)();

            return array_map($this->toResource(...), $bookmarks);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $bookmark = ($this->getBookmarkHandler)(new GetBookmarkQuery($id));

        return $this->toResource($bookmark);
    }

    private function toResource(Bookmark $bookmark): BookmarkResource
    {
        $article = $bookmark->getArticle();
        $feed = $article->getFeed();
        $category = $feed->getCategory();

        return new BookmarkResource(
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
