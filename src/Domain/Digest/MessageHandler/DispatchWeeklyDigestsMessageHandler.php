<?php

declare(strict_types=1);

namespace App\Domain\Digest\MessageHandler;

use App\Domain\Digest\Message\DispatchWeeklyDigestsMessage;
use App\Domain\Digest\Message\GenerateWeeklyDigestMessage;
use App\Domain\Digest\Port\DigestIntelligenceInterface;
use App\Domain\Digest\Service\WeeklyPeriod;
use App\Domain\Feed\Message\CrawlFeedMessage;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Feed;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

use function is_string;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class DispatchWeeklyDigestsMessageHandler
{
    private const string LEASE = '-15 minutes';

    public function __construct(private EntityManagerInterface $em, private MessageBusInterface $bus, private WeeklyPeriod $period, private ConsentPolicy $consent, private DigestIntelligenceInterface $ai)
    {
    }

    public function __invoke(DispatchWeeklyDigestsMessage $message): void
    {
        $cutoff = $this->period->cutoff(new DateTimeImmutable());
        // A unique owner/period key is the durable outbox. Ticks can overlap safely.
        $owners = $this->em->createQuery('SELECT u FROM '.User::class.' u WHERE u.deletedAt IS NULL AND u.emailVerifiedAt IS NOT NULL AND u.processingRestricted = false AND u.createdAt <= :cutoff')->setParameter('cutoff', $cutoff)->toIterable();
        foreach ($owners as $owner) {
            if (!$owner instanceof User || !$this->consent->allows($owner, 'newsletter', $cutoff) || !$this->consent->allows($owner, 'external_ai', $cutoff)) {
                continue;
            }
            $this->em->getConnection()->executeStatement('INSERT INTO weekly_digest (id, owner_id, period, status, attempts, items, updated_at) VALUES (:id, :owner, :period, :status, 0, :items, :now) ON CONFLICT (owner_id, period) DO NOTHING', [
                'id' => Uuid::v7()->toRfc4122(), 'owner' => $owner->getId()->toRfc4122(), 'period' => $cutoff->format('Y-m-d'), 'status' => 'queued', 'items' => '[]', 'now' => new DateTimeImmutable()->format('Y-m-d H:i:s'),
            ]);
        }
        $connection = $this->em->getConnection();
        $now = new DateTimeImmutable();
        // Never blindly retry an interrupted SMTP send: its delivery may already have happened.
        $connection->executeStatement("UPDATE weekly_digest SET status = 'uncertain' WHERE status = 'sending' AND updated_at < :cutoff", ['cutoff' => $now->modify('-10 minutes')->format('Y-m-d H:i:s')]);
        // Unfinished editions of earlier weeks are never delivered late, e.g. once inference gets configured.
        $connection->executeStatement(
            "UPDATE weekly_digest SET status = 'skipped', updated_at = :now WHERE period < :period AND status IN ('queued', 'generating', 'setup_pending', 'ready', 'selection_required')",
            ['now' => $now->format('Y-m-d H:i:s'), 'period' => $cutoff->format('Y-m-d')],
        );
        // The lease queues each digest at most once per window: slow workers cannot pile up duplicate
        // messages, and a failed generation waits for the next window before using another attempt.
        $states = $this->ai->configured() ? "'queued', 'ready', 'generating', 'setup_pending'" : "'queued', 'ready'";
        $ids = $connection->fetchFirstColumn(
            'UPDATE weekly_digest SET dispatched_at = :now WHERE id IN (SELECT id FROM weekly_digest WHERE period = :period AND status IN ('.$states.') AND (dispatched_at IS NULL OR dispatched_at < :leaseBefore) ORDER BY id LIMIT 100 FOR UPDATE SKIP LOCKED) RETURNING id',
            ['now' => $now->format('Y-m-d H:i:s'), 'period' => $cutoff->format('Y-m-d'), 'leaseBefore' => $now->modify(self::LEASE)->format('Y-m-d H:i:s')],
        );
        foreach ($ids as $id) {
            if (is_string($id)) {
                $this->bus->dispatch(new GenerateWeeklyDigestMessage($id));
            }
        }
        // Refresh RSS throughout the week; error feeds retry on the next hourly pass.
        /** @var list<Feed> $feeds */
        $feeds = $this->em->createQuery('SELECT f FROM '.Feed::class.' f WHERE f.organization IS NULL AND f.source = :source AND f.status != :paused AND (f.lastFetchedAt IS NULL OR f.lastFetchedAt < :before) ORDER BY f.lastFetchedAt ASC')
            ->setParameter('source', 'rss')->setParameter('paused', Feed::STATUS_PAUSED)->setParameter('before', new DateTimeImmutable('-1 hour'))->setMaxResults(100)->getResult();
        foreach ($feeds as $feed) {
            if ($feed instanceof Feed) {
                $this->bus->dispatch(new CrawlFeedMessage($feed->getId()->toRfc4122()));
            }
        }
    }
}
