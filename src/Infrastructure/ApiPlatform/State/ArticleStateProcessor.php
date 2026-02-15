<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use App\Domain\Article\Command\MarkArticleReadCommand;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Handler\MarkArticleReadHandler;
use App\Domain\Article\Query\GetArticleQuery;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\ArticleResource;

use function assert;

use DateTimeInterface;

use function is_string;
use function str_ends_with;

use Symfony\Bundle\SecurityBundle\Security;

/**
 * @implements ProcessorInterface<mixed, ArticleResource|null>
 */
final readonly class ArticleStateProcessor implements ProcessorInterface
{
    public function __construct(
        private MarkArticleReadHandler $markArticleReadHandler,
        private GetArticleHandler $getArticleHandler,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ArticleResource
    {
        $user = $this->security->getUser();
        assert($user instanceof User);
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof Patch) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            $uriTemplate = $operation->getUriTemplate() ?? '';
            $isRead = str_ends_with($uriTemplate, '/read');

            ($this->markArticleReadHandler)(new MarkArticleReadCommand(
                id: $id,
                isRead: $isRead,
                ownerId: $ownerId,
            ));

            $article = ($this->getArticleHandler)(new GetArticleQuery($id, $ownerId));
            $feed = $article->getFeed();
            $category = $feed->getCategory();

            return new ArticleResource(
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

        return null;
    }
}
