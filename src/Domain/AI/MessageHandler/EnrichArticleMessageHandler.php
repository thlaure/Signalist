<?php

declare(strict_types=1);

namespace App\Domain\AI\MessageHandler;

use App\Domain\AI\Message\EnrichArticleMessage;
use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Tag\Service\TagCatalog;
use App\Entity\Article;
use App\Entity\ArticleEmbedding;
use App\Entity\Organization;
use App\Infrastructure\Async\JobLock;

use function array_slice;
use function count;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class EnrichArticleMessageHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleIntelligenceInterface $intelligence,
        private ArticleTextCleaner $cleaner,
        private ArticleAnonymizer $anonymizer,
        private ConsentPolicy $consent,
        private LoggerInterface $logger,
        private ?UsageBudget $budget = null,
        private ?TagCatalog $tagCatalog = null,
        private ?JobLock $jobLock = null,
    ) {
    }

    public function __invoke(EnrichArticleMessage $message): void
    {
        if (!$this->jobLock instanceof JobLock) {
            $this->process($message);

            return;
        }
        $this->jobLock->run('article-'.$message->articleId, fn () => $this->process($message));
    }

    private function process(EnrichArticleMessage $message): void
    {
        if (!Uuid::isValid($message->articleId)) {
            return;
        }
        $article = $this->entityManager->find(Article::class, Uuid::fromString($message->articleId));

        if ($article instanceof Article) {
            $this->entityManager->refresh($article);
        }

        if (!$article instanceof Article || $article->getEnrichedAt() instanceof DateTimeImmutable) {
            return;
        }
        $owner = $article->getFeed()->getOwner();

        if (!$this->consent->allows($owner, 'external_ai')) {
            return;
        }
        $text = $this->anonymizer->anonymize(
            $this->cleaner->clean($article->getContent() ?? $article->getSummary() ?? $article->getTitle()),
            [$article->getAuthor() ?? '', $owner->getDisplayName(), $owner->getEmail()],
        );
        $chunks = $this->cleaner->chunks(mb_substr($text, 0, 60000));

        if ([] === $chunks) {
            return;
        }
        $this->budget?->aiCalls($owner, count($chunks) + 2, $article->getFeed()->getOrganization());
        $this->logger->info('AI processing started', ['articleId' => $message->articleId, 'characters' => mb_strlen($text), 'chunks' => count($chunks)]);
        $summary = $this->intelligence->summarize(implode("\n", $chunks));
        $tags = array_values(array_unique(array_filter(array_map(static fn (string $tag): string => mb_substr(trim($tag), 0, 60), $this->intelligence->tags($chunks[0])))));
        $embeddings = [];
        foreach ($chunks as $index => $chunk) {
            $vector = $this->intelligence->embed($chunk);

            if (1536 !== count($vector) || count(array_filter($vector, is_finite(...))) !== count($vector)) {
                throw new UnrecoverableMessageHandlingException('Embedding must contain 1536 finite values.');
            }
            $embeddings[] = new ArticleEmbedding()->setArticle($article)->setChunkIndex($index)->setChunkText($chunk)->setEmbedding($vector);
        }
        // Re-check consent after outbound work, before storing results.
        $this->entityManager->refresh($owner);

        if (!$this->consent->allows($owner, 'external_ai')) {
            return;
        }
        $this->entityManager->wrapInTransaction(function () use ($article, $summary, $tags, $embeddings): void {
            foreach ($article->getEmbeddings() as $old) {
                $this->entityManager->remove($old);
            }
            $article->setAiSummary(mb_substr($summary, 0, 20000))->setTags(!$article->getFeed()->getOrganization() instanceof Organization && $this->tagCatalog instanceof TagCatalog ? $this->tagCatalog->register($article->getFeed()->getOwner(), $tags) : array_slice($tags, 0, 20))->setEnrichedAt(new DateTimeImmutable());
            foreach ($embeddings as $embedding) {
                $this->entityManager->persist($embedding);
            }
        });
    }
}
