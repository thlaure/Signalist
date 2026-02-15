<?php

declare(strict_types=1);

namespace App\Domain\Auth\MessageHandler;

use App\Domain\Auth\Message\SendVerificationEmailMessage;
use App\Domain\Auth\Port\EmailVerificationTokenGeneratorInterface;

use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

#[AsMessageHandler]
final readonly class SendVerificationEmailMessageHandler
{
    public function __construct(
        private EmailVerificationTokenGeneratorInterface $tokenGenerator,
        private MailerInterface $mailer,
    ) {
    }

    public function __invoke(SendVerificationEmailMessage $message): void
    {
        $verificationUrl = $this->tokenGenerator->generateSignedUrl(
            $message->userId,
            $message->email,
        );

        $email = new Email()
            ->from('noreply@signalist.app')
            ->to($message->email)
            ->subject('Verify your Signalist email address')
            ->html($this->buildHtml($verificationUrl));

        $this->mailer->send($email);
    }

    private function buildHtml(string $verificationUrl): string
    {
        $escapedUrl = htmlspecialchars($verificationUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return <<<HTML
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" lang="en">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0" />
                <title>Verify your email</title>
            </head>
            <body style="margin: 0; padding: 0;" bgcolor="#ffffff">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td align="center" style="padding-top: 40px; padding-bottom: 40px; padding-left: 20px; padding-right: 20px;">
                            <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="600">
                                <tr>
                                    <td align="left" style="font-family: Arial, Helvetica, sans-serif; font-size: 28px; font-weight: bold; color: #333333; padding-bottom: 20px;">
                                        Welcome to Signalist!
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; color: #333333; padding-bottom: 30px;">
                                        Please verify your email address by clicking the button below:
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 10px; padding-bottom: 30px;">
                                        <table role="presentation" border="0" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td align="center" bgcolor="#1976d2" style="padding-top: 12px; padding-bottom: 12px; padding-left: 24px; padding-right: 24px;">
                                                    <a href="{$escapedUrl}" target="_blank" style="font-family: Arial, Helvetica, sans-serif; font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none;">Verify Email</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #666666; padding-bottom: 10px;">
                                        This link will expire in 24 hours.
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left" style="font-family: Arial, Helvetica, sans-serif; font-size: 14px; color: #666666;">
                                        If you did not create an account, you can safely ignore this email.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>
            HTML;
    }
}
