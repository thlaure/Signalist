<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Handler;

use App\Domain\Article\Exception\ArticleNotFoundException;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Bookmark\Command\CreateBookmarkCommand;
use App\Domain\Bookmark\Exception\ArticleAlreadyBookmarkedException;
use App\Domain\Bookmark\Port\BookmarkRepositoryInterface;
use App\Entity\Article;
use App\Entity\Bookmark;

final readonly class CreateBookmarkHandler
{
    public function __construct(
        private BookmarkRepositoryInterface $bookmarkRepository,
        private ArticleRepositoryInterface $articleRepository,
    ) {
    }

    public function __invoke(CreateBookmarkCommand $command): string
    {
        $article = $this->articleRepository->find($command->articleId);

        if (!$article instanceof Article) {
            throw new ArticleNotFoundException($command->articleId);
        }

        if ($article->getFeed()->getOwner()->getId()->toRfc4122() !== $command->ownerId) {
            throw new ArticleNotFoundException($command->articleId);
        }

        $existingBookmark = $this->bookmarkRepository->findByArticle($command->articleId);

        if ($existingBookmark instanceof Bookmark) {
            throw new ArticleAlreadyBookmarkedException($command->articleId);
        }

        $bookmark = new Bookmark();
        $bookmark->setArticle($article);
        $bookmark->setNotes($command->notes);

        $this->bookmarkRepository->save($bookmark);

        return $bookmark->getId()->toRfc4122();
    }
}
