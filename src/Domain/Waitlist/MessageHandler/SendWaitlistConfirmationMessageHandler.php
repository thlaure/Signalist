<?php

declare(strict_types=1);

namespace App\Domain\Waitlist\MessageHandler;

use App\Domain\Waitlist\Message\SendWaitlistConfirmationMessage;
use App\Entity\WaitlistEntry;
use App\Infrastructure\Waitlist\WaitlistToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class SendWaitlistConfirmationMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private WaitlistToken $token, private MailerInterface $mailer, private string $frontendUrl, private string $mailSender = 'noreply@signalist.app')
    {
    }

    public function __invoke(SendWaitlistConfirmationMessage $message): void
    {
        $entry = $this->entityManager->find(WaitlistEntry::class, Uuid::fromString($message->entryId));

        if (!$entry instanceof WaitlistEntry || $entry->getConfirmedAt() instanceof DateTimeImmutable) {
            return;
        }
        $base = rtrim($this->frontendUrl, '/').'/waitlist?id='.$message->entryId;
        $confirm = $base.'&action=confirm&signature='.$this->token->sign($entry, 'confirm');
        $withdraw = $base.'&action=withdraw&signature='.$this->token->sign($entry, 'withdraw');
        $this->mailer->send(new Email()->from($this->mailSender)->to($entry->getEmail())
            ->subject('Confirm your Signalist early access request')
            ->text("Confirm your request within 7 days:\n".$confirm."\n\nWithdraw and erase your request at any time:\n".$withdraw));
    }
}
