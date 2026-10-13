<?php

declare(strict_types=1);

namespace App\Domain\Newsletter\MessageHandler;

use App\Domain\Newsletter\Message\GenerateNewsletterMessage;
use App\Domain\Newsletter\Service\NewsletterContentBuilder;
use App\Domain\Privacy\Service\ConsentPolicy;
use App\Entity\Article;
use App\Entity\NewsletterRun;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class GenerateNewsletterMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private NewsletterContentBuilder $builder, private ConsentPolicy $consent, private MailerInterface $mailer, private string $mailSender = 'noreply@signalist.app')
    {
    }

    public function __invoke(GenerateNewsletterMessage $message): void
    {
        $run = $this->entityManager->find(NewsletterRun::class, Uuid::fromString($message->runId));

        if (!$run instanceof NewsletterRun) {
            return;
        }
        $claimed = $this->entityManager->wrapInTransaction(function () use ($run): bool {
            $this->entityManager->refresh($run, LockMode::PESSIMISTIC_WRITE);

            if ('queued' !== $run->getStatus()) {
                return false;
            }
            $run->setStatus('generating');

            return true;
        });

        if (!$claimed) {
            return;
        }
        $newsletter = $run->getNewsletter();
        $owner = $newsletter->getOwner();

        if ($owner->isDeleted() || $owner->isProcessingRestricted() || ($run->shouldDeliver() && !$this->consent->allows($owner, 'newsletter'))) {
            $run->setStatus('cancelled');
            $this->entityManager->flush();

            return;
        }
        /** @var list<Article> $articles */
        $articles = $this->entityManager->createQueryBuilder()->select('a')->from(Article::class, 'a')->join('a.bookmark', 'b')->join('a.feed', 'f')
            ->where('f.owner = :owner')->andWhere('f.organization IS NULL')->setParameter('owner', $owner)->orderBy('b.createdAt', 'DESC')->setMaxResults(500)->getQuery()->getResult();
        $html = $this->builder->build($newsletter->getTitle(), $articles, $newsletter->getReadingMinutes());
        $newsletter->generate($html);

        $this->entityManager->refresh($owner);

        if ($owner->isDeleted() || $owner->isProcessingRestricted() || ($run->shouldDeliver() && !$this->consent->allows($owner, 'newsletter'))) {
            $run->setStatus('cancelled');
            $this->entityManager->flush();

            return;
        }

        if ($run->shouldDeliver()) {
            // Persist before SMTP. An interrupted send remains uncertain and is not blindly retried.
            $run->setStatus('sending');
            $this->entityManager->flush();
            $this->mailer->send(new Email()->from($this->mailSender)->to($owner->getEmail())->subject($newsletter->getTitle())->html($html));
            $run->setStatus('sent');
        } else {
            $run->setStatus('draft');
        }
        $this->entityManager->flush();
    }
}
