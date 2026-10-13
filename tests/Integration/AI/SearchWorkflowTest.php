<?php

declare(strict_types=1);

namespace App\Tests\Integration\AI;

use App\Domain\AI\Port\ArticleIntelligenceInterface;
use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Search\DTO\SearchInput;
use App\Domain\Search\Handler\SearchHandler;
use App\Domain\Search\Message\RunArticleSearchMessage;
use App\Domain\Search\MessageHandler\RunArticleSearchMessageHandler;
use App\Domain\Search\Port\SearchRepositoryInterface;
use App\Domain\Search\Service\RankFusion;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use Symfony\Component\Messenger\MessageBusInterface;

final class SearchWorkflowTest extends DatabaseTestCase
{
    public function testHybridSearchQueuesInferenceAndRechecksResultOwnership(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $owner->setPlan('team', new DateTimeImmutable('+1 month'));
        $this->entityManager->persist($owner);
        $this->entityManager->persist(new UserConsent($owner, 'external_ai', true));
        $category = new Category()->setOwner($owner)->setName('News')->setSlug('news');
        $feed = new Feed()->setOwner($owner)->setCategory($category)->setTitle('News')->setUrl('https://example.com/rss');
        $article = new Article()->setFeed($feed)->setTitle('Useful source')->setGuid('guid')->setUrl('https://example.com/source');
        foreach ([$category, $feed, $article] as $entity) {
            $this->entityManager->persist($entity);
        } $this->entityManager->flush();
        $repository = $this->createMock(SearchRepositoryInterface::class);
        $repository->method('fulltext')->willReturn([$article->getId()->toRfc4122()]);
        $repository->method('semantic')->willReturn([$article->getId()->toRfc4122()]);
        $provider = $this->createMock(ArticleIntelligenceInterface::class);
        $provider->method('configured')->willReturn(true);
        $provider->expects($this->once())->method('embed')->with($this->logicalNot($this->stringContains('owner@example.com')))->willReturn(array_fill(0, 1536, 0.1));
        $consent = new ConsentPolicy($this->entityManager);
        $handler = new SearchHandler($this->entityManager, $repository, $consent, self::getContainer()->get(MessageBusInterface::class), $provider);
        $search = $handler->create($owner, new SearchInput('Useful source owner@example.com', 'hybrid'));
        $this->assertSame('pending', $search->getStatus());
        $worker = new RunArticleSearchMessageHandler($this->entityManager, $repository, $provider, new ArticleAnonymizer(), $consent, new RankFusion(), new UsageBudget($this->entityManager->getConnection(), new PlanCatalog()));
        $message = new RunArticleSearchMessage($search->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $result = $handler->get($owner, $search->getId()->toRfc4122());
        $this->assertSame('complete', $result['status']);
        $this->assertCount(1, $result['items']);
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        $this->entityManager->persist($other);
        $feed->setOwner($other);
        $this->entityManager->flush();
        $this->assertSame([], $handler->get($owner, $search->getId()->toRfc4122())['items']);
    }
}
