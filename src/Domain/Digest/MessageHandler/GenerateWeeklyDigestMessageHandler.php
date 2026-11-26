<?php

declare(strict_types=1);

namespace App\Domain\Digest\MessageHandler;

use App\Domain\AI\Service\ArticleAnonymizer;
use App\Domain\AI\Service\ArticleTextCleaner;
use App\Domain\Digest\Message\GenerateWeeklyDigestMessage;
use App\Domain\Digest\Port\DigestIntelligenceInterface;
use App\Domain\Digest\Service\DigestLimits;
use App\Domain\Digest\Service\SourcePolicy;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\WeeklyDigest;
use App\Infrastructure\Async\JobLock;

use function count;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

use function in_array;

use const PHP_URL_SCHEME;
use const PHP_URL_USER;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;
use Throwable;

#[AsMessageHandler]
final readonly class GenerateWeeklyDigestMessageHandler
{
    public function __construct(private EntityManagerInterface $em, private JobLock $lock, private SourcePolicy $sources, private ConsentPolicy $consent, private DigestIntelligenceInterface $ai, private ArticleTextCleaner $cleaner, private ArticleAnonymizer $anonymizer, private DigestLimits $limits, private MailerInterface $mailer, private string $frontendUrl, private string $mailSender = 'noreply@signalist.app', private ?LoggerInterface $logger = null)
    {
    }

    public function __invoke(GenerateWeeklyDigestMessage $message): void
    {
        $this->lock->run('weekly-digest-'.$message->digestId, function () use ($message): void {
            $digest = $this->em->find(WeeklyDigest::class, Uuid::fromString($message->digestId));

            if (!$digest instanceof WeeklyDigest) {
                return;
            }
            $this->em->refresh($digest);

            if (!in_array($digest->getStatus(), ['queued', 'generating', 'setup_pending', 'ready'], true)) {
                return;
            }
            $owner = $digest->getOwner();

            if (!$this->allowed($owner)) {
                $digest->setStatus('cancelled');
                $this->em->flush();

                return;
            }

            if ($this->sources->needsSelection($owner)) {
                $digest->setStatus('selection_required');
                $this->em->flush();

                return;
            }

            if ('ready' !== $digest->getStatus()) {
                $items = $this->articles($digest);

                if ([] === $items) {
                    $digest->setStatus('empty');
                    $this->em->flush();

                    return;
                }

                if (!$this->ai->configured()) {
                    $digest->setStatus('setup_pending');
                    $this->em->flush();

                    return;
                }

                if ($digest->getAttempts() >= $this->limits->attemptsPerDigest) {
                    $digest->setStatus('failed');
                    $this->em->flush();

                    return;
                }
                $digest->attempt();
                $this->em->flush();

                try {
                    $input = array_map(static fn (array $item): array => ['title' => $item['title'], 'text' => $item['text']], $items);
                    $summaries = $this->ai->summarize($input);

                    if (count($summaries) !== count($items)) {
                        throw new RuntimeException('Incomplete digest summaries.');
                    }
                    $completed = [];
                    foreach ($items as $index => $item) {
                        $summary = $summaries[$index];

                        if ('' === trim($summary) || mb_strlen($summary) > 1000) {
                            throw new RuntimeException('Invalid digest summary.');
                        }
                        $completed[] = ['title' => $item['title'], 'url' => $item['url'], 'source' => $item['source'], 'summary' => strip_tags($summary)];
                    }

                    if (!$this->allowed($owner)) {
                        $digest->setStatus('cancelled');
                        $this->em->flush();

                        return;
                    }
                    $digest->complete($completed);
                    $this->em->flush();
                } catch (Throwable $exception) {
                    // No rethrow: Messenger retries would burn the attempt budget within seconds.
                    // The weekly dispatcher re-queues the digest once its dispatch lease expires.
                    $digest->setStatus($digest->getAttempts() >= $this->limits->attemptsPerDigest ? 'failed' : 'queued');
                    $this->em->flush();
                    $this->logger?->warning('Weekly digest generation failed', ['digestId' => $message->digestId, 'attempts' => $digest->getAttempts(), 'error' => $exception::class]);

                    return;
                }
            }

            if (!$this->allowed($owner)) {
                $digest->setStatus('cancelled');
                $this->em->flush();

                return;
            }
            $html = '<h1>Signalist · '.$digest->getPeriod().'</h1>';
            foreach ($digest->getItems() as $item) {
                $html .= '<article><h2><a href="'.$this->escape($item['url']).'">'.$this->escape($item['title']).'</a></h2><p>'.$this->escape($item['summary']).'</p><p>'.$this->escape($item['source']).'</p></article>';
            }
            $html .= '<p><a href="'.$this->escape(rtrim($this->frontendUrl, '/')).'/settings">Email preferences / Préférences email</a></p>';
            $digest->setStatus('sending');
            $this->em->flush();

            try {
                $this->mailer->send(new Email()->from($this->mailSender)->to($owner->getEmail())->subject('Signalist · '.$digest->getPeriod())->html($html));
                $digest->sent();
                $this->em->flush();
            } catch (Throwable) {
                $digest->setStatus('uncertain');
                $this->em->flush();

                throw new RuntimeException('Digest delivery is uncertain; automatic resend disabled.');
            }
        });
    }

    /** @phpstan-impure Consent can change while inference runs. */
    private function allowed(User $owner): bool
    {
        $this->em->refresh($owner);

        return $owner->isEmailVerified() && $this->consent->allows($owner, 'newsletter') && $this->consent->allows($owner, 'external_ai');
    }

    /** @return list<array{title: string, url: string, source: string, text: string}> */
    private function articles(WeeklyDigest $digest): array
    {
        $end = new DateTimeImmutable($digest->getPeriod().' 08:00:00', new DateTimeZone('UTC'));
        $items = [];
        $seen = [];
        // Keep citations from earlier completed editions from resurfacing under new RSS GUIDs.
        foreach ($this->em->getRepository(WeeklyDigest::class)->findBy(['owner' => $digest->getOwner()], ['period' => 'DESC'], 100) as $previous) {
            if ($previous->getId()->equals($digest->getId()) || $previous->getPeriod() >= $digest->getPeriod()) {
                continue;
            }
            foreach ($previous->getItems() as $item) {
                $seen[$item['url']] = true;
            }
        }
        foreach ($this->sources->active($digest->getOwner()) as $feed) {
            /** @var list<Article> $articles */
            $articles = $this->em->createQuery('SELECT a FROM '.Article::class.' a WHERE a.feed = :feed AND a.createdAt >= :start AND a.createdAt < :end AND (a.publishedAt IS NULL OR (a.publishedAt >= :start AND a.publishedAt < :end)) ORDER BY a.createdAt DESC, a.id DESC')
                ->setParameter('feed', $feed)->setParameter('start', $end->modify('-1 week'))->setParameter('end', $end)->setMaxResults($this->limits->articlesPerSource * 5)->getResult();
            $count = 0;
            foreach ($articles as $article) {
                if (!$article instanceof Article || !in_array(parse_url($article->getUrl(), PHP_URL_SCHEME), ['http', 'https'], true) || null !== parse_url($article->getUrl(), PHP_URL_USER)) {
                    continue;
                }
                $url = preg_replace('/#.*$/', '', $article->getUrl()) ?? $article->getUrl();

                if (isset($seen[$url])) {
                    continue;
                }
                $text = $this->anonymizer->anonymize($this->cleaner->clean(mb_substr($article->getSummary() ?? $article->getContent() ?? '', 0, 16000)), [$article->getAuthor() ?? '', $digest->getOwner()->getDisplayName()]);

                if ('' === $text) {
                    continue;
                }
                $seen[$url] = true;
                $items[] = ['title' => mb_substr($this->anonymizer->anonymize($this->cleaner->clean($article->getTitle()), [$article->getAuthor() ?? '', $digest->getOwner()->getDisplayName()]), 0, 300), 'url' => $url, 'source' => $this->cleaner->clean($feed->getTitle()), 'text' => mb_substr($text, 0, $this->limits->charactersPerArticle)];

                if (count($items) >= $this->limits->articlesPerDigest) {
                    return $items;
                }

                if (++$count >= $this->limits->articlesPerSource) {
                    break;
                }
            }
        }

        return $items;
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
