<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Bookmark\Command\CreateBookmarkCommand;
use App\Domain\Bookmark\Command\DeleteBookmarkCommand;
use App\Domain\Bookmark\DTO\Input\CreateBookmarkInput;
use App\Domain\Bookmark\Handler\CreateBookmarkHandler;
use App\Domain\Bookmark\Handler\DeleteBookmarkHandler;
use App\Domain\Bookmark\Handler\GetBookmarkHandler;
use App\Domain\Bookmark\Query\GetBookmarkQuery;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\BookmarkResource;

use function assert;

use DateTimeInterface;

use function is_string;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<CreateBookmarkInput, BookmarkResource|null>
 */
final readonly class BookmarkStateProcessor implements ProcessorInterface
{
    public function __construct(
        private CreateBookmarkHandler $createBookmarkHandler,
        private DeleteBookmarkHandler $deleteBookmarkHandler,
        private GetBookmarkHandler $getBookmarkHandler,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?BookmarkResource
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof Post && $data instanceof CreateBookmarkInput) {
            $id = ($this->createBookmarkHandler)(new CreateBookmarkCommand(
                articleId: $data->articleId,
                ownerId: $ownerId,
                notes: $data->notes,
            ));

            $bookmark = ($this->getBookmarkHandler)(new GetBookmarkQuery($id, $ownerId));
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

        if ($operation instanceof Delete) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            ($this->deleteBookmarkHandler)(new DeleteBookmarkCommand($id, $ownerId));

            return null;
        }

        return null;
    }
}
