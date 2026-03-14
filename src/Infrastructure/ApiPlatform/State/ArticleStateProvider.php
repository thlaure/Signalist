<?php

declare(strict_types=1);

namespace App\Infrastructure\ApiPlatform\State;

use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Handler\ListArticlesHandler;
use App\Domain\Article\Query\GetArticleQuery;
use App\Domain\Article\Query\ListArticlesQuery;
use App\Entity\Article;
use App\Entity\User;
use App\Infrastructure\ApiPlatform\Resource\ArticleResource;
use App\Infrastructure\ApiPlatform\Resource\PaginatedArticlesResponse;

use function assert;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

use function is_string;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @implements ProviderInterface<ArticleResource|PaginatedArticlesResponse>
 */
final readonly class ArticleStateProvider implements ProviderInterface
{
    public function __construct(
        private GetArticleHandler $getArticleHandler,
        private ListArticlesHandler $listArticlesHandler,
        private RequestStack $requestStack,
        private Security $security,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ArticleResource|PaginatedArticlesResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new AccessDeniedException();
        }
        $ownerId = $user->getId()->toRfc4122();

        if ($operation instanceof CollectionOperationInterface) {
            $request = $this->requestStack->getCurrentRequest();

            $feedId = $request?->query->get('feedId');
            $categoryId = $request?->query->get('categoryId');
            $isReadParam = $request?->query->get('isRead');
            $search = $request?->query->get('search');
            $pageParam = $request?->query->get('page');
            $limitParam = $request?->query->get('limit');

            $isRead = null;

            if ($isReadParam !== null) {
                $isRead = filter_var($isReadParam, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }

            $page = max(1, (int) ($pageParam ?? 1));
            $limit = min(100, max(1, (int) ($limitParam ?? 20)));

            $query = new ListArticlesQuery(
                ownerId: $ownerId,
                feedId: is_string($feedId) ? $feedId : null,
                categoryId: is_string($categoryId) ? $categoryId : null,
                isRead: $isRead,
                search: is_string($search) ? $search : null,
                page: $page,
                limit: $limit,
            );

            $result = ($this->listArticlesHandler)($query);

            return new PaginatedArticlesResponse(
                items: array_map(self::toResource(...), $result->items),
                total: $result->total,
                page: $result->page,
                limit: $result->limit,
                pages: $result->pages,
            );
        }

        $id = $uriVariables['id'] ?? '';
        assert(is_string($id));

        $article = ($this->getArticleHandler)(new GetArticleQuery($id, $ownerId));

        return self::toResource($article);
    }

    public static function toResource(Article $article): ArticleResource
    {
        $output = ArticleOutput::fromEntity($article);

        return new ArticleResource(
            id: $output->id,
            title: $output->title,
            url: $output->url,
            summary: $output->summary,
            content: $output->content,
            author: $output->author,
            imageUrl: $output->imageUrl,
            isRead: $output->isRead,
            publishedAt: $output->publishedAt,
            createdAt: $output->createdAt,
            feedId: $output->feedId,
            feedTitle: $output->feedTitle,
            categoryId: $output->categoryId,
            categoryName: $output->categoryName,
            categoryColor: $output->categoryColor,
        );
    }
}
