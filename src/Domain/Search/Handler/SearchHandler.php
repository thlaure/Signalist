<?php

declare(strict_types=1);

namespace App\Domain\Search\Handler;

use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\Article\DTO\Output\ArticleOutput;
use App\Domain\Privacy\Exception\ConsentRequiredException;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Search\DTO\SearchInput;
use App\Domain\Search\Message\RunArticleSearchMessage;
use App\Domain\Search\Port\SearchRepositoryInterface;
use App\Domain\Spotlight\Message\AnswerArticleQuestionMessage;
use App\Domain\Spotlight\Port\ArticleQuestionInterface;
use App\Entity\Article;
use App\Entity\ArticleSearch;
use App\Entity\Organization;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

final readonly class SearchHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private SearchRepositoryInterface $repository, private ConsentPolicy $consent, private MessageBusInterface $bus, private ArticleIntelligenceInterface $intelligence, private ?ArticleQuestionInterface $questions = null)
    {
    }

    public function create(User $user, SearchInput $input): ArticleSearch
    {
        if ('fulltext' !== $input->mode && !('question' === $input->mode ? $this->questions?->configured() : $this->intelligence->configured())) {
            throw new ServiceUnavailableHttpException(null, 'AI provider setup is pending.');
        }

        if ('fulltext' !== $input->mode && !$this->consent->allows($user, 'external_ai')) {
            throw new ConsentRequiredException('external_ai');
        }
        $search = new ArticleSearch($user, trim($input->query), $input->mode);

        if ('fulltext' === $input->mode) {
            $search->complete($this->repository->fulltext($user, $input->query));
        }
        $this->entityManager->persist($search);
        $this->entityManager->flush();

        if ('fulltext' !== $input->mode) {
            $this->bus->dispatch('question' === $input->mode ? new AnswerArticleQuestionMessage($search->getId()->toRfc4122()) : new RunArticleSearchMessage($search->getId()->toRfc4122()));
        }

        return $search;
    }

    /** @return array{id: string, status: string, answer: ?string, items: list<ArticleOutput>} */
    public function get(User $user, string $id): array
    {
        $search = Uuid::isValid($id) ? $this->entityManager->find(ArticleSearch::class, Uuid::fromString($id)) : null;

        if (!$search instanceof ArticleSearch || !$search->getOwner()->getId()->equals($user->getId())) {
            throw new NotFoundHttpException();
        }
        $items = [];
        foreach ($search->getArticleIds() as $articleId) {
            $article = $this->entityManager->find(Article::class, Uuid::fromString($articleId));

            if ($article instanceof Article && !$article->getFeed()->getOrganization() instanceof Organization && $article->getFeed()->getOwner()->getId()->equals($user->getId())) {
                $items[] = ArticleOutput::fromEntity($article);
            }
        }

        return ['id' => $id, 'status' => $search->getStatus(), 'answer' => $search->getAnswer(), 'items' => $items];
    }
}
