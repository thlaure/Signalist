<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Auth\MessageHandler;

use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\MessageHandler\SendVerificationEmailMessageHandler;
use App\Domain\Auth\Port\EmailVerificationTokenGeneratorInterface;
use App\Domain\Auth\Port\UserRepositoryInterface;
use App\Entity\User;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final class SendVerificationEmailMessageHandlerTest extends TestCase
{
    public function testDeletedAccountCannotReceiveQueuedVerificationMail(): void
    {
        $user = new User()->setEmail('deleted@example.com')->setPassword('hash')->setDeletedAt(new DateTimeImmutable());
        $users = $this->createMock(UserRepositoryInterface::class);
        $users->method('find')->willReturn($user);
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->never())->method('send');
        new SendVerificationEmailMessageHandler($this->createMock(EmailVerificationTokenGeneratorInterface::class), $mailer, 'sender@example.com', $users)(new SendVerificationEmailMessage($user->getId()->toRfc4122(), 'deleted@example.com'));
    }

    public function testMailUsesConfiguredSenderAndEscapesSignedLink(): void
    {
        $tokens = $this->createMock(EmailVerificationTokenGeneratorInterface::class);
        $tokens->method('generateSignedUrl')->willReturn('https://app.example.com/verify?a=1&b="quoted"');
        $mailer = $this->createMock(MailerInterface::class);
        $mailer->expects($this->once())->method('send')->willReturnCallback(function (Email $mail): void {
            $this->assertSame('sender@example.com', $mail->getFrom()[0]->getAddress());
            $this->assertSame('owner@example.com', $mail->getTo()[0]->getAddress());
            $body = $mail->getHtmlBody();
            $this->assertIsString($body);
            $this->assertStringContainsString('&amp;b=&quot;quoted&quot;', $body);
        });
        new SendVerificationEmailMessageHandler($tokens, $mailer, 'sender@example.com')(new SendVerificationEmailMessage('fixture-id', 'owner@example.com'));
    }
}
