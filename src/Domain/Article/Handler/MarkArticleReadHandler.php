<?php

declare(strict_types=1);

namespace App\Domain\Article\Handler;

use App\Domain\Article\Command\MarkArticleReadCommand;
use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Entity\Article;

final readonly class MarkArticleReadHandler
{
    public function __construct(
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(MarkArticleReadCommand $command): void
    {
        $article = $this->articleRepository->find($command->id);

        if (!$article instanceof Article) {
            throw new ArticleNotFoundException($command->id);
        }

        $article->setIsRead($command->isRead);

        $this->articleRepository->save($article);
    }
}
