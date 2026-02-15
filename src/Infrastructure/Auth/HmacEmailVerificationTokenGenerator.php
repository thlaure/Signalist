<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Port\EmailVerificationTokenGeneratorInterface;

use function sprintf;

final readonly class HmacEmailVerificationTokenGenerator implements EmailVerificationTokenGeneratorInterface
{
    private const int TTL_SECONDS = 86400;

    public function __construct(
        private string $appSecret,
        private string $frontendUrl,
    ) {
    }

    public function generateSignedUrl(string $userId, string $email): string
    {
        $expiresAt = time() + self::TTL_SECONDS;
        $signature = $this->computeSignature($userId, $email, $expiresAt);

        return sprintf(
            '%s/verify-email?userId=%s&email=%s&expiresAt=%d&signature=%s',
            rtrim($this->frontendUrl, '/'),
            urlencode($userId),
            urlencode($email),
            $expiresAt,
            urlencode($signature),
        );
    }

    public function validateSignedUrl(string $userId, string $email, int $expiresAt, string $signature): bool
    {
        if ($expiresAt < time()) {
            return false;
        }

        $expectedSignature = $this->computeSignature($userId, $email, $expiresAt);

        return hash_equals($expectedSignature, $signature);
    }

    private function computeSignature(string $userId, string $email, int $expiresAt): string
    {
        $payload = sprintf('%s|%s|%d', $userId, $email, $expiresAt);

        return hash_hmac('sha256', $payload, $this->appSecret);
    }
}
