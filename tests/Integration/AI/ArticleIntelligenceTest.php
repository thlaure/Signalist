<?php

declare(strict_types=1);

namespace App\Tests\Integration\AI;

use App\Domain\AI\Message\EnrichArticleMessage;
use App\Domain\AI\MessageHandler\EnrichArticleMessageHandler;
use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\ArticleEmbedding;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Infrastructure\Persistence\Search\PgvectorSearchRepository;
use App\Tests\Integration\DatabaseTestCase;
use Psr\Log\NullLogger;

final class ArticleIntelligenceTest extends DatabaseTestCase
{
    private function article(string $email, string $title): Article
    {
        $user = new User()->setEmail($email)->setPassword('hash');
        $category = new Category()->setName('News')->setSlug('news')->setOwner($user);
        $feed = new Feed()->setTitle('Feed')->setUrl('https://example.com/rss')->setCategory($category)->setOwner($user);
        $article = new Article()->setTitle($title)->setGuid('guid')->setUrl('https://example.com/article')->setFeed($feed)->setAuthor('Jane Doe')
            ->setContent('<p>Jane Doe wrote to private@example.com about quantum computing.</p>')->setSummary('Original publisher summary');
        foreach ([$user, $category, $feed, $article] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();

        return $article;
    }

    public function testEnrichmentNeedsConsentAndKeepsSourceSummaryAndAttribution(): void
    {
        $article = $this->article('owner@example.com', 'Quantum computing');
        $provider = $this->createMock(ArticleIntelligenceInterface::class);
        $provider->expects($this->once())->method('summarize')->with($this->callback(static fn (string $text): bool => !str_contains($text, 'private@example.com') && !str_contains($text, 'Jane Doe')))->willReturn('Anonymized quantum summary');
        $provider->expects($this->once())->method('tags')->willReturn(['Quantum', 'Quantum', ' ']);
        $provider->expects($this->once())->method('embed')->willReturn(array_fill(0, 1536, 0.25));
        $handler = new EnrichArticleMessageHandler($this->entityManager, $provider, new ArticleTextCleaner(), new ArticleAnonymizer(), new ConsentPolicy($this->entityManager), new NullLogger());
        $message = new EnrichArticleMessage($article->getId()->toRfc4122());
        $handler($message);
        $this->assertNull($article->getAiSummary());
        $this->entityManager->persist(new UserConsent($article->getFeed()->getOwner(), 'external_ai', true));
        $this->entityManager->flush();
        $handler($message);
        $handler($message);
        $this->assertSame('Original publisher summary', $article->getSummary());
        $this->assertSame('Anonymized quantum summary', $article->getAiSummary());
        $this->assertSame(['Quantum'], $article->getTags());
        $this->assertSame('https://example.com/article', $article->getUrl());
        $this->assertSame(1, $this->entityManager->getRepository(ArticleEmbedding::class)->count(['article' => $article]));
    }

    public function testVectorAndFulltextSearchNeverReturnOtherOwners(): void
    {
        $ownerArticle = $this->article('owner@example.com', 'Quantum physics');
        $otherArticle = $this->article('other@example.com', 'Quantum private notes');
        foreach ([$ownerArticle, $otherArticle] as $article) {
            $embedding = new ArticleEmbedding()->setArticle($article)->setEmbedding(array_fill(0, 1536, 0.25));
            $this->entityManager->persist($embedding);
        }
        $this->entityManager->flush();
        $repository = new PgvectorSearchRepository($this->entityManager->getConnection());
        $owner = $ownerArticle->getFeed()->getOwner();
        $this->assertSame([$ownerArticle->getId()->toRfc4122()], $repository->fulltext($owner, 'Quantum'));
        $this->assertSame([$ownerArticle->getId()->toRfc4122()], $repository->semantic($owner, array_fill(0, 1536, 0.25)));
    }
}
