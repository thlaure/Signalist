<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\MessageHandler;

use App\Domain\Newsletter\Message\DispatchNewsletterSchedulesMessage;
use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Newsletter;
use App\Entity\NewsletterRun;

use const DATE_ATOM;

use DateTimeImmutable;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class DispatchNewsletterSchedulesMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private MessageBusInterface $bus, private ConsentPolicy $consent)
    {
    }

    public function __invoke(DispatchNewsletterSchedulesMessage $message): void
    {
        $now = new DateTimeImmutable();
        $cutoff = new DateTimeImmutable('-15 minutes')->format('Y-m-d H:i:s');
        $this->entityManager->getConnection()->executeStatement("UPDATE newsletter_run SET status = CASE WHEN status = 'sending' THEN 'uncertain' ELSE 'queued' END WHERE status IN ('generating', 'sending') AND created_at < :cutoff", ['cutoff' => $cutoff]);
        /** @var list<Newsletter> $due */
        $due = $this->entityManager->createQuery('SELECT n FROM '.Newsletter::class.' n WHERE n.nextRunAt <= :now')
            ->setParameter('now', $now)->setMaxResults(100)->getResult();
        foreach ($due as $newsletter) {
            $this->entityManager->wrapInTransaction(function () use ($newsletter, $now): void {
                $this->entityManager->refresh($newsletter, LockMode::PESSIMISTIC_WRITE);

                if (null === $newsletter->getNextRunAt() || $newsletter->getNextRunAt() > $now) {
                    return;
                }

                if ($this->consent->allows($newsletter->getOwner(), 'newsletter')) {
                    $run = new NewsletterRun($newsletter, $newsletter->getNextRunAt()->format(DATE_ATOM), true);
                    $this->entityManager->persist($run);
                }
                $newsletter->advanceSchedule($now);
            });
        }
        // Persisted queued runs form an outbox: a failed dispatch is retried on the next tick.
        $queued = $this->entityManager->getRepository(NewsletterRun::class)->findBy(['status' => 'queued'], ['createdAt' => 'ASC'], 100);
        foreach ($queued as $run) {
            $this->bus->dispatch(new GenerateNewsletterMessage($run->getId()->toRfc4122()));
        }
    }
}
