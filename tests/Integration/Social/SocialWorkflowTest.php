<?php

declare(strict_types=1);

namespace App\Tests\Integration\Social;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Billing\Service\PlanCatalog;
use App\Domain\Billing\Service\UsageBudget;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Domain\Social\Message\CrosspostMessage;
use App\Domain\Social\Message\GenerateSocialRecapMessage;
use App\Domain\Social\MessageHandler\CrosspostMessageHandler;
use App\Domain\Social\MessageHandler\GenerateSocialRecapMessageHandler;
use App\Domain\Social\Port\CrosspostAdapterInterface;
use App\Domain\Social\Service\PlatformFormatter;
use App\Domain\Social\Service\SocialRecapContentBuilder;
use App\Entity\Article;
use App\Entity\Bookmark;
use App\Entity\Category;
use App\Entity\Crosspost;
use App\Entity\ExternalAccount;
use App\Entity\Feed;
use App\Entity\SocialRecap;
use App\Entity\User;
use App\Infrastructure\AI\UnconfiguredRecapIntelligence;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Messenger\MessageBusInterface;

final class SocialWorkflowTest extends DatabaseTestCase
{
    public function testCuratedDraftIsPrivateScopedAndPreservesSourceUrls(): void
    {
        $owner = $this->user('owner@example.com');
        $other = $this->user('other@example.com');
        foreach ([$owner, $other] as $user) {
            $category = new Category()->setOwner($user)->setName('News')->setSlug('news');
            $feed = new Feed()->setOwner($user)->setCategory($category)->setTitle('News')->setUrl('https://example.com/feed');
            $article = new Article()->setFeed($feed)->setGuid('guid')->setTitle($user === $owner ? 'Useful facts' : 'Other private title')->setSummary('Contact owner@example.com')->setUrl('https://example.com/source');
            $bookmark = new Bookmark()->setArticle($article)->setNotes('Private notes never enter prompts');
            foreach ([$category, $feed, $article, $bookmark] as $entity) {
                $this->entityManager->persist($entity);
            }
        }
        $recap = new SocialRecap($owner, 'linkedin', new DateTimeImmutable('-1 day'), new DateTimeImmutable('+1 day'));
        $this->entityManager->persist($recap);
        $this->entityManager->flush();
        $worker = new GenerateSocialRecapMessageHandler($this->entityManager, new SocialRecapContentBuilder(new ArticleTextCleaner(), new ArticleAnonymizer(), new UnconfiguredRecapIntelligence(), new PlatformFormatter()), new ConsentPolicy($this->entityManager), new UsageBudget($this->entityManager->getConnection(), new PlanCatalog()));
        $worker(new GenerateSocialRecapMessage($recap->getId()->toRfc4122()));
        $this->assertSame('draft', $recap->getStatus());
        $text = implode('\n', $recap->getPosts());
        $this->assertStringContainsString('Useful facts', $text);
        $this->assertStringContainsString('https://example.com/source', $text);
        $this->assertStringNotContainsString('Other private title', $text);
        $this->assertStringNotContainsString('owner@example.com', $text);
        $this->assertStringNotContainsString('Private notes', $text);
    }

    public function testCrosspostIsIdempotentAfterConfirmedPublication(): void
    {
        $post = $this->post();
        $adapter = $this->createMock(CrosspostAdapterInterface::class);
        $adapter->expects($this->once())->method('publish')->willReturn('remote-post');
        $worker = new CrosspostMessageHandler($this->entityManager, $adapter, self::getContainer()->get(MessageBusInterface::class));
        $message = new CrosspostMessage($post->getId()->toRfc4122());
        $worker($message);
        $worker($message);
        $this->assertSame('published', $post->getStatus());
        $this->assertSame(['remote-post'], $post->getPublishedIds());
    }

    public function testLostResponseDoesNotAutomaticallyPublishAgain(): void
    {
        $post = $this->post();
        $adapter = $this->createMock(CrosspostAdapterInterface::class);
        $adapter->expects($this->once())->method('publish')->willThrowException(new RuntimeException('Lost response'));
        $worker = new CrosspostMessageHandler($this->entityManager, $adapter, self::getContainer()->get(MessageBusInterface::class));
        $message = new CrosspostMessage($post->getId()->toRfc4122());

        try {
            $worker($message);
            $this->fail('Expected uncertain outcome');
        } catch (RuntimeException $error) {
            $this->assertSame('Lost response', $error->getMessage());
        }
        $worker($message);
        $this->assertSame('uncertain', $post->getStatus());
        $this->assertSame([], $post->getPublishedIds());
    }

    public function testReconnectedIdentityCannotReceiveOldApproval(): void
    {
        $post = $this->post();
        $post->getAccount()->connected('ciphertext', new DateTimeImmutable('+1 hour'), 'different-person');
        $this->entityManager->flush();
        $adapter = $this->createMock(CrosspostAdapterInterface::class);
        $adapter->expects($this->never())->method('publish');
        new CrosspostMessageHandler($this->entityManager, $adapter, self::getContainer()->get(MessageBusInterface::class))(new CrosspostMessage($post->getId()->toRfc4122()));
        $this->assertSame('cancelled', $post->getStatus());
    }

    private function user(string $email): User
    {
        $user = new User()->setEmail($email)->setPassword('hash');
        $this->entityManager->persist($user);

        return $user;
    }

    private function post(): Crosspost
    {
        $account = new ExternalAccount($this->user('owner@example.com'), 'x');
        $account->connected('ciphertext', new DateTimeImmutable('+1 hour'), 'remote-person');
        $post = new Crosspost($account, 'remote-person', ['Approved content'], 'stable-key');
        $this->entityManager->persist($account);
        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return $post;
    }
}
