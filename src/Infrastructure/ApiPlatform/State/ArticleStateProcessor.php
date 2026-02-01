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
use App\Infrastructure\ApiPlatform\Resource\ArticleResource;

use function assert;

use DateTimeInterface;

use function is_string;
use function str_ends_with;

/**
 * @implements ProcessorInterface<mixed, ArticleResource|null>
 */
final readonly class ArticleStateProcessor implements ProcessorInterface
{
    public function __construct(
        private MarkArticleReadHandler $markArticleReadHandler,
        private GetArticleHandler $getArticleHandler,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ArticleResource
    {
        if ($operation instanceof Patch) {
            $id = $uriVariables['id'] ?? '';
            assert(is_string($id));

            $uriTemplate = $operation->getUriTemplate() ?? '';
            $isRead = str_ends_with($uriTemplate, '/read');

            ($this->markArticleReadHandler)(new MarkArticleReadCommand(
                id: $id,
                isRead: $isRead,
            ));

            $article = ($this->getArticleHandler)(new GetArticleQuery($id));
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
