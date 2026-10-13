<?php

declare(strict_types=1);

namespace App\Tests\Integration\Waitlist;

use App\Domain\Waitlist\Handler\WaitlistHandler;
use App\Domain\Waitlist\Message\SendWaitlistConfirmationMessage;
use App\Domain\Waitlist\MessageHandler\SendWaitlistConfirmationMessageHandler;
use App\Entity\WaitlistEntry;
use App\Infrastructure\Waitlist\WaitlistToken;
use App\Tests\Integration\DatabaseTestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Email;

final class WaitlistWorkflowTest extends DatabaseTestCase
{
    public function testNormalizedDeduplicationQueuesOneConfirmationWithWithdrawalLink(): void
    {
        $bus = $this->createMock(MessageBusInterface::class);
        $bus->expects($this->once())->method('dispatch')->willReturnCallback(static fn (object $message): Envelope => new Envelope($message));
        $handler = new WaitlistHandler($this->entityManager, $bus);
        $handler->join(' OWNER@example.com ');
        $handler->join('owner@example.com');
        $entry = $this->entityManager->getRepository(WaitlistEntry::class)->findOneBy(['email' => 'owner@example.com']);
        $this->assertInstanceOf(WaitlistEntry::class, $entry);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->willReturnCallback(function (Email $email): void {
            $this->assertSame('sender@example.com', $email->getFrom()[0]->getAddress());
            $this->assertSame('owner@example.com', $email->getTo()[0]->getAddress());
            $body = $email->getTextBody();
            $this->assertIsString($body);
            $this->assertStringContainsString('action=confirm', $body);
            $this->assertStringContainsString('action=withdraw', $body);
        });
        new SendWaitlistConfirmationMessageHandler($this->entityManager, new WaitlistToken('test-secret'), $mailer, 'https://app.example.com', 'sender@example.com')(new SendWaitlistConfirmationMessage($entry->getId()->toRfc4122()));
    }
}
