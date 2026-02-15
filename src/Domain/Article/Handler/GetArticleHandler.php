<?php

declare(strict_types=1);

namespace App\Domain\Article\Handler;

use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\GetArticleQuery;
use App\Entity\Article;

final readonly class GetArticleHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(GetArticleQuery $query): Article
    {
        $article = $this->articleRepository->find($query->id);

        if (!$article instanceof Article) {
            throw new ArticleNotFoundException($query->id);
        }

        if ($article->getFeed()->getOwner()->getId()->toRfc4122() !== $query->ownerId) {
            throw new ArticleNotFoundException($query->id);
        }

        return $article;
    }
}
