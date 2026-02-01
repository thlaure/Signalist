<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Handler\ListArticlesHandler;
use App\Domain\Article\Query\GetArticleQuery;
use App\Domain\Article\Query\ListArticlesQuery;
use App\Entity\Article;
use App\Infrastructure\ApiPlatform\Resource\ArticleResource;

use function assert;

use DateTimeInterface;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

use function is_string;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @implements ProviderInterface<ArticleResource>
 */
final readonly class ArticleStateProvider implements ProviderInterface
{
    public function __construct(
        private GetArticleHandler $getArticleHandler,
        private ListArticlesHandler $listArticlesHandler,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return ArticleResource|array<int, ArticleResource>
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ArticleResource|array
    {
        if ($operation instanceof CollectionOperationInterface) {
            $request = $this->requestStack->getCurrentRequest();

            $feedId = $request?->query->get('feedId');
            $categoryId = $request?->query->get('categoryId');
            $isReadParam = $request?->query->get('isRead');

            $isRead = null;

            if ($isReadParam !== null) {
                $isRead = filter_var($isReadParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $query = new ListArticlesQuery(
                feedId: is_string($feedId) ? $feedId : null,
                categoryId: is_string($categoryId) ? $categoryId : null,
                isRead: $isRead,
            );

            $articles = ($this->listArticlesHandler)($query);

            return array_map($this->toResource(...), $articles);
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $article = ($this->getArticleHandler)(new GetArticleQuery($id));

        return $this->toResource($article);
    }

    private function toResource(Article $article): ArticleResource
    {
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
}
