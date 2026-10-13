<?php

declare(strict_types=1);

namespace App\Infrastructure\MCP;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Article\Handler\GetArticleHandler;
use App\Domain\Article\Port\ArticleRepositoryInterface;
use App\Domain\Article\Query\GetArticleQuery;
use App\Domain\Category\Port\CategoryRepositoryInterface;
use App\Domain\Feed\Handler\GetFeedHandler;
use App\Domain\Feed\Query\GetFeedQuery;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final readonly class ArticleTools
{
    public function __construct(private Security $security, private GetArticleHandler $getArticle, private GetFeedHandler $getFeed, private ArticleRepositoryInterface $articles, private CategoryRepositoryInterface $categories, private ArticleTextCleaner $cleaner, private ArticleAnonymizer $anonymizer)
    {
    }

    /** @return list<array<string, mixed>> */
    public function searchArticles(string $query): array
    {
        $owner = $this->user();

        return array_values(array_map($this->output(...), $this->articles->findAll(['ownerId' => $owner->getId()->toRfc4122(), 'search' => mb_substr($query, 0, 500)], 1, 20)));
    }

    /** @return array<string, mixed> */
    public function getArticle(string $id): array
    {
        return $this->output(($this->getArticle)(new GetArticleQuery($id, $this->user()->getId()->toRfc4122())));
    }

    /** @return list<array<string, mixed>> */
    public function listCategories(): array
    {
        return array_values(array_map(static fn (Category $category): array => ['id' => $category->getId()->toRfc4122(), 'name' => $category->getName()], $this->categories->findAllByOwner($this->user()->getId()->toRfc4122())));
    }

    /** @return array<string, mixed> */
    public function getFeedSummary(string $id): array
    {
        $ownerId = $this->user()->getId()->toRfc4122();
        $feed = ($this->getFeed)(new GetFeedQuery($id, $ownerId));

        return ['id' => $id, 'title' => $feed->getTitle(), 'articles' => array_map($this->output(...), $this->articles->findAll(['ownerId' => $ownerId, 'feedId' => $id], 1, 10))];
    }

    /** @return array<string, mixed> */
    private function output(Article $article): array
    {
        $owner = $this->user();
        $summary = $this->anonymizer->anonymize($this->cleaner->clean($article->getAiSummary() ?? $article->getSummary() ?? ''), [$article->getAuthor() ?? '', $owner->getEmail(), $owner->getDisplayName()]);

        return ['id' => $article->getId()->toRfc4122(), 'title' => $article->getTitle(), 'url' => $article->getUrl(), 'summary' => mb_substr($summary, 0, 2000), 'tags' => $article->getTags()];
    }

    private function user(): User
    {
        $user = $this->security->getUser();

        if (!$user instanceof User || $user->isDeleted()) {
            throw new AccessDeniedException();
        }

        return $user;
    }
}
