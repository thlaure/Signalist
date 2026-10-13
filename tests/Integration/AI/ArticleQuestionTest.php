<?php

declare(strict_types=1);

namespace App\Tests\Integration\AI;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Search\Port\SearchRepositoryInterface;
use App\Domain\Spotlight\Message\AnswerArticleQuestionMessage;
use App\Domain\Spotlight\MessageHandler\AnswerArticleQuestionMessageHandler;
use App\Domain\Spotlight\Port\ArticleQuestionInterface;
use App\Entity\Article;
use App\Entity\ArticleSearch;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;

final class ArticleQuestionTest extends DatabaseTestCase
{
    public function testOnlyOwnedSourcesReachProviderAndConsentIsRechecked(): void
    {
        $owner = new User()->setEmail('owner@example.com')->setPassword('hash');
        $owner->setPlan('team', new DateTimeImmutable('+1 month'));
        $other = new User()->setEmail('other@example.com')->setPassword('hash');
        foreach ([$owner, $other, new UserConsent($owner, 'external_ai', true)] as $entity) {
            $this->entityManager->persist($entity);
        }
        $ids = [];
        foreach ([$owner, $other] as $user) {
            $category = new Category()->setOwner($user)->setName('News')->setSlug('news');
            $feed = new Feed()->setOwner($user)->setCategory($category)->setUrl('https://example.com/feed')->setTitle('News');
            $article = new Article()->setFeed($feed)->setGuid('guid')->setTitle($user === $owner ? 'Useful facts' : 'Foreign private title')->setSummary('Contact '.$user->getEmail())->setUrl('https://example.com/source');
            foreach ([$category, $feed, $article] as $entity) {
                $this->entityManager->persist($entity);
            }
            $ids[] = $article->getId()->toRfc4122();
        }
        $search = new ArticleSearch($owner, 'Useful facts owner@example.com', 'question');
        $this->entityManager->persist($search);
        $this->entityManager->flush();
        $repository = $this->createMock(SearchRepositoryInterface::class);
        $repository->method('fulltext')->willReturn($ids);
        $provider = $this->createMock(ArticleQuestionInterface::class);
        $provider->expects($this->once())->method('answer')->willReturnCallback(function (string $question, string $sources): string {
            $this->assertStringNotContainsString('owner@example.com', $question);
            $this->assertStringNotContainsString('owner@example.com', $sources);
            $this->assertStringNotContainsString('Foreign private title', $sources);
            $this->assertStringContainsString('[1] Useful facts', $sources);

            return 'Supported answer [1]';
        });
        $worker = new AnswerArticleQuestionMessageHandler($this->entityManager, $repository, $provider, new ArticleAnonymizer(), new ArticleTextCleaner(), new ConsentPolicy($this->entityManager), new UsageBudget($this->entityManager->getConnection(), new PlanCatalog()));
        $message = new AnswerArticleQuestionMessage($search->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $this->assertSame('Supported answer [1]', $search->getAnswer());
        $this->assertSame([$ids[0]], $search->getArticleIds());
    }
}
