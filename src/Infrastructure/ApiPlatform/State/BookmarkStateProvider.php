<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Bookmark\DTO\Output\BookmarkOutput;
use App\Domain\Bookmark\Handler\GetBookmarkHandler;
use App\Domain\Bookmark\Handler\ListBookmarksHandler;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Domain\Bookmark\Query\ListBookmarksQuery;
use App\Entity\Bookmark;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\BookmarkResource;

use function assert;
use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProviderInterface<BookmarkResource>
 */
final readonly class BookmarkStateProvider implements ProviderInterface
{
    public function __construct(
        private GetBookmarkHandler $getBookmarkHandler,
        private ListBookmarksHandler $listBookmarksHandler,
        private Security $security,
    ) {
    }

    /**
     * @return BookmarkResource|array<int, BookmarkResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): BookmarkResource|array
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof CollectionOperationInterface) {
            $bookmarks = ($this->listBookmarksHandler)(new ListBookmarksQuery($ownerId));

            return array_map(self::toResource(...), $bookmarks);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $bookmark = ($this->getBookmarkHandler)(new GetBookmarkQuery($id, $ownerId));

        return self::toResource($bookmark);
    }

    public static function toResource(Bookmark $bookmark): BookmarkResource
    {
        $output = BookmarkOutput::fromEntity($bookmark);

        return new BookmarkResource(
            id: $output->id,
            notes: $output->notes,
            createdAt: $output->createdAt,
            articleId: $output->articleId,
            articleTitle: $output->articleTitle,
            articleUrl: $output->articleUrl,
            feedId: $output->feedId,
            feedTitle: $output->feedTitle,
            categoryId: $output->categoryId,
            categoryName: $output->categoryName,
        );
    }
}
