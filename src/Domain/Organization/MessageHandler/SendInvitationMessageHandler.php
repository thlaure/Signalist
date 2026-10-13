<?php

declare(strict_types=1);

namespace App\Domain\Organization\MessageHandler;

use App\Domain\Organization\Message\SendInvitationMessage;
use App\Entity\OrganizationInvitation;
use App\Infrastructure\Organization\InvitationToken;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final readonly class SendInvitationMessageHandler
{
    public function __construct(private EntityManagerInterface $entityManager, private InvitationToken $token, private MailerInterface $mailer, private string $mailSender = 'noreply@signalist.app')
    {
    }

    public function __invoke(SendInvitationMessage $message): void
    {
        $invitation = $this->entityManager->find(OrganizationInvitation::class, Uuid::fromString($message->invitationId));

        if (!$invitation instanceof OrganizationInvitation || $invitation->isAccepted() || $invitation->getExpiresAt() < new DateTimeImmutable()) {
            return;
        }
        $this->mailer->send(new Email()->from($this->mailSender)->to($invitation->getEmail())->subject('Signalist workspace invitation')
            ->text('You were invited to '.$invitation->getOrganization()->getName().". Sign in using this email address and accept within seven days:\n".$this->token->url($invitation)));
    }
}
