<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Auth;

use App\Infrastructure\Auth\HmacEmailVerificationTokenGenerator;

use const PHP_URL_QUERY;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class HmacEmailVerificationTokenGeneratorTest extends TestCase
{
    private HmacEmailVerificationTokenGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new HmacEmailVerificationTokenGenerator(
            appSecret: 'test-secret-key',
            frontendUrl: 'http://localhost:5173',
        );
    }

    public function testGenerateSignedUrlContainsAllParams(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';

        $url = $this->generator->generateSignedUrl($userId, $email);

        $this->assertStringStartsWith('http://localhost:5173/verify-email?', $url);
        $this->assertStringContainsString('userId=' . urlencode($userId), $url);
        $this->assertStringContainsString('email=' . urlencode($email), $url);
        $this->assertStringContainsString('expiresAt=', $url);
        $this->assertStringContainsString('signature=', $url);
    }

    public function testValidateSignedUrlWithValidSignature(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';

        $url = $this->generator->generateSignedUrl($userId, $email);
        $params = $this->extractQueryParams($url);

        $result = $this->generator->validateSignedUrl(
            $params['userId'],
            $params['email'],
            (int) $params['expiresAt'],
            $params['signature'],
        );

        $this->assertTrue($result);
    }

    public function testValidateSignedUrlWithTamperedEmailReturnsFalse(): void
    {
        $userId = Uuid::v7()->toRfc4122();

        $url = $this->generator->generateSignedUrl($userId, 'test@signalist.app');
        $params = $this->extractQueryParams($url);

        $result = $this->generator->validateSignedUrl(
            $params['userId'],
            'tampered@signalist.app',
            (int) $params['expiresAt'],
            $params['signature'],
        );

        $this->assertFalse($result);
    }

    public function testValidateSignedUrlWithExpiredTokenReturnsFalse(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';
        $pastExpiry = time() - 3600;

        $result = $this->generator->validateSignedUrl(
            $userId,
            $email,
            $pastExpiry,
            'any-signature',
        );

        $this->assertFalse($result);
    }

    public function testValidateSignedUrlWithDifferentSecretReturnsFalse(): void
    {
        $userId = Uuid::v7()->toRfc4122();
        $email = 'test@signalist.app';

        $url = $this->generator->generateSignedUrl($userId, $email);
        $params = $this->extractQueryParams($url);

        $otherGenerator = new HmacEmailVerificationTokenGenerator(
            appSecret: 'different-secret',
            frontendUrl: 'http://localhost:5173',
        );

        $result = $otherGenerator->validateSignedUrl(
            $params['userId'],
            $params['email'],
            (int) $params['expiresAt'],
            $params['signature'],
        );

        $this->assertFalse($result);
    }

    /**
     * @return array<string, string>
     */
    private function extractQueryParams(string $url): array
    {
        $query = parse_url($url, PHP_URL_QUERY);
        self::assertIsString($query);

        parse_str($query, $params);

        $result = [];
        foreach ($params as $key => $value) {
            self::assertIsString($value);
            $result[(string) $key] = $value;
        }

        return $result;
    }
}
