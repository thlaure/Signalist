<?php

declare(strict_types=1);

namespace App\Tests\Integration\Digest;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Digest\Handler\SourceHandler;
use App\Domain\Digest\Message\DispatchWeeklyDigestsMessage;
use App\Domain\Digest\Message\GenerateWeeklyDigestMessage;
use App\Domain\Digest\MessageHandler\DispatchWeeklyDigestsMessageHandler;
use App\Domain\Digest\MessageHandler\GenerateWeeklyDigestMessageHandler;
use App\Domain\Digest\Port\DigestIntelligenceInterface;
use App\Domain\Digest\Service\DigestLimits;
use App\Domain\Digest\Service\SourcePolicy;
use App\Domain\Digest\Service\WeeklyPeriod;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Feed;
use App\Entity\User;
use App\Entity\UserConsent;
use App\Entity\WeeklyDigest;
use App\Infrastructure\Async\JobLock;
use App\Tests\Integration\DatabaseTestCase;
use DateTimeImmutable;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

final class WeeklyDigestWorkflowTest extends DatabaseTestCase
{
    private User $owner;

    private Feed $feed;

    private WeeklyDigest $digest;

    private DigestIntelligenceInterface&MockObject $ai;

    private MailerInterface&MockObject $mailer;

    /** @var list<string> IDs of generation messages queued by the dispatcher */
    private array $queued = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->owner = new User()->setEmail('digest@example.test')->setPassword('hash')->setEmailVerifiedAt(new DateTimeImmutable());
        $category = new Category()->setOwner($this->owner)->setName('Sources')->setSlug('sources');
        $this->feed = new Feed()->setOwner($this->owner)->setCategory($category)->setUrl('https://example.com/rss')->setTitle('Source');
        $period = new WeeklyPeriod()->cutoff(new DateTimeImmutable())->format('Y-m-d');
        $this->digest = new WeeklyDigest($this->owner, $period);
        foreach ([$this->owner, $category, $this->feed, $this->digest, new UserConsent($this->owner, 'newsletter', true), new UserConsent($this->owner, 'external_ai', true)] as $entity) {
            $this->entityManager->persist($entity);
        }
        $this->entityManager->flush();
        $this->ai = $this->createMock(DigestIntelligenceInterface::class);
        $this->ai->method('configured')->willReturn(true);
        $this->mailer = $this->createMock(MailerInterface::class);
    }

    public function testGenerationUsesRecentUnbookmarkedArticlesAndDoesNotSendTwice(): void
    {
        $this->article('one', 'https://example.com/article#one');
        $this->article('two', 'https://example.com/article#two');
        $this->article('old', 'https://example.com/old', '-20 days');
        $this->ai->expects($this->once())->method('summarize')->willReturnCallback(function (array $input): array {
            $this->assertCount(1, $input);
            $this->assertArrayNotHasKey('url', $input[0]);
            $this->assertStringNotContainsString('person@example.test', $input[0]['text']);
            $this->assertLessThanOrEqual(2000, mb_strlen((string) $input[0]['text']));

            return ['A supported summary.'];
        });
        $this->mailer->expects($this->once())->method('send')->with($this->callback(function (Email $email): bool {
            $body = $email->getHtmlBody();
            $this->assertIsString($body);
            $this->assertStringContainsString('https://example.com/article', $body);
            $this->assertStringContainsString('http://frontend.test/settings', $body);

            return true;
        }));
        $handler = $this->handler();
        $message = new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122());
        $handler($message);
        $handler($message);
        $this->assertSame('sent', $this->digest->getStatus());
        $this->assertCount(1, $this->digest->getItems());
        $this->assertSame(1, $this->digest->getAttempts());
        $this->assertNotNull($this->digest->getSentAt());
    }

    public function testEmptyWeekNeverCallsAiOrSendsEmail(): void
    {
        $this->ai->expects($this->never())->method('summarize');
        $this->mailer->expects($this->never())->method('send');
        $this->handler()(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('empty', $this->digest->getStatus());
    }

    public function testConsentWithdrawalCancelsBeforeInference(): void
    {
        $this->article('one', 'https://example.com/article');
        $this->entityManager->persist(new UserConsent($this->owner, 'newsletter', false));
        $this->entityManager->flush();
        $this->ai->expects($this->never())->method('summarize');
        $this->mailer->expects($this->never())->method('send');
        $this->handler()(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('cancelled', $this->digest->getStatus());
    }

    public function testFailuresConsumeAtMostThreeInferenceAttempts(): void
    {
        $this->article('one', 'https://example.com/article');
        $this->ai->expects($this->exactly(3))->method('summarize')->willThrowException(new RuntimeException('fixture failure'));
        $this->mailer->expects($this->never())->method('send');
        $handler = $this->handler();
        // Failures are not rethrown, so Messenger cannot burn the attempt budget with its own retries.
        $handler(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('queued', $this->digest->getStatus());
        for ($i = 0; $i < 3; ++$i) {
            $handler(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        }
        $this->assertSame(3, $this->digest->getAttempts());
        $this->assertSame('failed', $this->digest->getStatus());
    }

    public function testDispatchLeaseQueuesEachDigestOncePerWindow(): void
    {
        $handler = $this->dispatcher();
        $handler(new DispatchWeeklyDigestsMessage());
        $handler(new DispatchWeeklyDigestsMessage());
        $this->assertSame([$this->digest->getId()->toRfc4122()], $this->queued);

        $this->entityManager->getConnection()->executeStatement('UPDATE weekly_digest SET dispatched_at = :expired', ['expired' => new DateTimeImmutable('-16 minutes')->format('Y-m-d H:i:s')]);
        $handler(new DispatchWeeklyDigestsMessage());
        $this->assertCount(2, $this->queued);
    }

    public function testEarlierWeeksAreSkippedInsteadOfDeliveredLate(): void
    {
        $previous = new WeeklyDigest($this->owner, new DateTimeImmutable($this->digest->getPeriod())->modify('-1 week')->format('Y-m-d'));
        $previous->setStatus('setup_pending');
        $this->entityManager->persist($previous);
        $this->entityManager->flush();

        $this->dispatcher()(new DispatchWeeklyDigestsMessage());
        $this->entityManager->refresh($previous);
        $this->assertSame('skipped', $previous->getStatus());
        $this->assertSame([$this->digest->getId()->toRfc4122()], $this->queued);
    }

    public function testInterruptedDeliveryIsNotAutomaticallyRetried(): void
    {
        $this->article('one', 'https://example.com/article');
        $this->ai->expects($this->once())->method('summarize')->willReturn(['Summary']);
        $this->mailer->expects($this->once())->method('send')->willThrowException(new RuntimeException('SMTP failure'));
        $handler = $this->handler();

        try {
            $handler(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        } catch (RuntimeException) {
        }
        $handler(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('uncertain', $this->digest->getStatus());
        $this->assertCount(1, $this->digest->getItems());
    }

    public function testDowngradeRequiresSelectionWithoutDeletingHistory(): void
    {
        for ($i = 0; $i < 3; ++$i) {
            $feed = new Feed()->setOwner($this->owner)->setCategory($this->feed->getCategory())->setUrl('https://example.com/'.$i)->setTitle('Extra');
            $this->entityManager->persist($feed);
        }
        $this->entityManager->flush();
        $this->ai->expects($this->never())->method('summarize');
        $this->mailer->expects($this->never())->method('send');
        $this->handler()(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('selection_required', $this->digest->getStatus());
        self::getContainer()->get(SourceHandler::class)->select($this->owner, [$this->feed->getId()->toRfc4122()]);
        $this->assertSame('queued', $this->digest->getStatus());
        $this->assertCount(4, self::getContainer()->get(SourcePolicy::class)->all($this->owner));
        $this->assertCount(1, self::getContainer()->get(SourcePolicy::class)->active($this->owner));
    }

    public function testRepeatedScheduleTicksCreateOneRunPerOwnerAndPeriod(): void
    {
        $this->entityManager->getConnection()->executeStatement("UPDATE \"user\" SET created_at = '2020-01-01' WHERE id = :id", ['id' => $this->owner->getId()->toRfc4122()]);
        $this->entityManager->remove($this->digest);
        $this->entityManager->flush();
        $this->entityManager->getConnection()->executeStatement("UPDATE user_consent SET created_at = '2020-01-01' WHERE user_id = :id", ['id' => $this->owner->getId()->toRfc4122()]);
        $this->entityManager->clear();
        $handler = new DispatchWeeklyDigestsMessageHandler($this->entityManager, self::getContainer()->get(MessageBusInterface::class), new WeeklyPeriod(), self::getContainer()->get(ConsentPolicy::class), $this->ai);
        $handler(new DispatchWeeklyDigestsMessage());
        $handler(new DispatchWeeklyDigestsMessage());
        $this->assertSame(1, $this->entityManager->getRepository(WeeklyDigest::class)->count(['owner' => $this->owner]));
    }

    public function testConsentGrantedAfterCutoffWaitsForNextWeek(): void
    {
        $this->entityManager->remove($this->digest);
        $this->entityManager->flush();
        $this->entityManager->getConnection()->executeStatement("UPDATE \"user\" SET created_at = '2020-01-01' WHERE id = :id", ['id' => $this->owner->getId()->toRfc4122()]);
        $handler = new DispatchWeeklyDigestsMessageHandler($this->entityManager, self::getContainer()->get(MessageBusInterface::class), new WeeklyPeriod(), self::getContainer()->get(ConsentPolicy::class), $this->ai);
        $handler(new DispatchWeeklyDigestsMessage());
        $this->assertSame(0, $this->entityManager->getRepository(WeeklyDigest::class)->count(['owner' => $this->owner]));
    }

    public function testMissingInferenceSetupDoesNotConsumeBudgetOrFakeSummaries(): void
    {
        $this->article('one', 'https://example.com/article');
        $this->ai = $this->createMock(DigestIntelligenceInterface::class);
        $this->ai->method('configured')->willReturn(false);
        $this->ai->expects($this->never())->method('summarize');
        $this->mailer->expects($this->never())->method('send');
        $this->handler()(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('setup_pending', $this->digest->getStatus());
        $this->assertSame(0, $this->digest->getAttempts());
        $this->assertSame([], $this->digest->getItems());
    }

    public function testWithdrawalDuringInferenceDiscardsResultsAndPreventsDelivery(): void
    {
        $this->article('one', 'https://example.com/article');
        $this->ai->expects($this->once())->method('summarize')->willReturnCallback(function (): array {
            $this->entityManager->persist(new UserConsent($this->owner, 'external_ai', false));
            $this->entityManager->flush();

            return ['Summary'];
        });
        $this->mailer->expects($this->never())->method('send');
        $this->handler()(new GenerateWeeklyDigestMessage($this->digest->getId()->toRfc4122()));
        $this->assertSame('cancelled', $this->digest->getStatus());
        $this->assertSame([], $this->digest->getItems());
    }

    private function article(string $guid, string $url, string $offset = '-1 day'): void
    {
        $article = new Article()->setFeed($this->feed)->setGuid($guid)->setTitle('Facts')->setUrl($url)->setSummary(str_repeat('Useful facts person@example.test ', 200));
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        $end = new DateTimeImmutable($this->digest->getPeriod().' 08:00:00');
        $this->entityManager->getConnection()->executeStatement('UPDATE article SET created_at = :date WHERE id = :id', ['date' => $end->modify($offset)->format('Y-m-d H:i:s'), 'id' => $article->getId()->toRfc4122()]);
    }

    private function dispatcher(): DispatchWeeklyDigestsMessageHandler
    {
        $this->queued = [];
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->method('dispatch')->willReturnCallback(function (object $message): Envelope {
            if ($message instanceof GenerateWeeklyDigestMessage) {
                $this->queued[] = $message->digestId;
            }

            return new Envelope($message);
        });

        return new DispatchWeeklyDigestsMessageHandler($this->entityManager, $bus, new WeeklyPeriod(), self::getContainer()->get(ConsentPolicy::class), $this->ai);
    }

    private function handler(): GenerateWeeklyDigestMessageHandler
    {
        return new GenerateWeeklyDigestMessageHandler($this->entityManager, self::getContainer()->get(JobLock::class), self::getContainer()->get(SourcePolicy::class), self::getContainer()->get(ConsentPolicy::class), $this->ai, new ArticleTextCleaner(), new ArticleAnonymizer(), new DigestLimits(), $this->mailer, 'http://frontend.test');
    }
}
