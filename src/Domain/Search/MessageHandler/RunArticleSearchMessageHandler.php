<?php

declare(strict_types=1);

namespace App\Domain\Search\MessageHandler;

use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Search\Message\RunArticleSearchMessage;
use App\Domain\Search\Port\SearchRepositoryInterface;
use App\Domain\Search\Service\RankFusion;
use App\Entity\ArticleSearch;
use App\Infrastructure\Async\JobLock;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class RunArticleSearchMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private SearchRepositoryInterface $repository, private ArticleIntelligenceInterface $intelligence, private ArticleAnonymizer $anonymizer, private ConsentPolicy $consent, private RankFusion $fusion, private ?UsageBudget $budget = null, private ?JobLock $jobLock = null)
    {
    }

    public function __invoke(RunArticleSearchMessage $message): void
    {
        if (!$this->jobLock instanceof JobLock) {
            $this->process($message);

            return;
        }
        $this->jobLock->run('search-'.$message->searchId, fn () => $this->process($message));
    }

    private function process(RunArticleSearchMessage $message): void
    {
        $search = $this->entityManager->find(ArticleSearch::class, Uuid::fromString($message->searchId));

        if ($search instanceof ArticleSearch) {
            $this->entityManager->refresh($search);
        }

        if (!$search instanceof ArticleSearch || 'pending' !== $search->getStatus()) {
            return;
        }
        $owner = $search->getOwner();

        if (!$this->consent->allows($owner, 'external_ai')) {
            $search->fail();
            $this->entityManager->flush();

            return;
        }

        try {
            $this->budget?->aiCalls($owner, 1);
            $query = $this->anonymizer->anonymize($search->getQuery(), [$owner->getEmail(), $owner->getDisplayName()]);
            $vector = $this->intelligence->embed($query);
            $semantic = $this->repository->semantic($owner, $vector);
            $this->entityManager->refresh($owner);

            if (!$this->consent->allows($owner, 'external_ai')) {
                $search->fail();
            } else {
                $search->complete('hybrid' === $search->getMode() ? $this->fusion->combine($this->repository->fulltext($owner, $search->getQuery()), $semantic) : $semantic);
            }
            $this->entityManager->flush();
        } catch (UnrecoverableMessageHandlingException|PlanLimitReachedException $exception) {
            $search->fail();
            $this->entityManager->flush();

            throw $exception;
        }
    }
}
