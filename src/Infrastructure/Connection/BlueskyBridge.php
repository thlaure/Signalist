<?php

declare(strict_types=1);

namespace App\Infrastructure\Connection;

use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Port\BlueskyGatewayInterface;
use DateTimeImmutable;

use function is_array;
use function is_string;

use const JSON_THROW_ON_ERROR;
use const PHP_URL_SCHEME;

use function strlen;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Process\Process;
use Throwable;

final readonly class BlueskyBridge implements BlueskyGatewayInterface
{
    public function __construct(private string $projectDir, private string $nodeBinary, private string $databaseUrl, private string $encodedKey, private string $privateKey, private string $backendUrl, private string $frontendUrl)
    {
    }

    public function configured(): bool
    {
        return is_executable($this->nodeBinary) && '' !== $this->privateKey && '' !== $this->encodedKey && 'https' === parse_url($this->backendUrl, PHP_URL_SCHEME);
    }

    public function authorize(string $ownerId, string $attemptId, string $handle): string
    {
        return $this->string($this->run(['action' => 'authorize', 'ownerId' => $ownerId, 'attemptId' => $attemptId, 'handle' => $handle]), 'url');
    }

    public function callback(string $ownerId, string $attemptId, array $parameters): OAuthTokens
    {
        $data = $this->run(['action' => 'callback', 'ownerId' => $ownerId, 'attemptId' => $attemptId, 'params' => $parameters]);

        return new OAuthTokens('sdk-managed-session', null, new DateTimeImmutable($this->string($data, 'expiresAt')), $this->string($data, 'did'));
    }

    public function recordKey(): string
    {
        return $this->string($this->run(['action' => 'key'], false), 'rkey');
    }

    public function post(string $ownerId, string $did, string $text, string $recordKey, string $createdAt): string
    {
        return $this->string($this->run(['action' => 'post', 'ownerId' => $ownerId, 'did' => $did, 'text' => $text, 'rkey' => $recordKey, 'createdAt' => $createdAt]), 'uri');
    }

    public function revoke(string $ownerId, string $did): void
    {
        $this->run(['action' => 'revoke', 'ownerId' => $ownerId, 'did' => $did]);
    }

    public function metadata(bool $keys = false): array
    {
        return $this->run(['action' => $keys ? 'jwks' : 'metadata']);
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array<string, mixed>
     */
    private function run(array $input, bool $requireConfiguration = true): array
    {
        if ($requireConfiguration && !$this->configured()) {
            throw new UnrecoverableMessageHandlingException('Bluesky setup is pending.');
        }
        $process = new Process([$this->nodeBinary, $this->projectDir.'/integrations/bluesky/bridge.mjs'], $this->projectDir, [
            'DATABASE_URL' => $this->databaseUrl, 'CREDENTIAL_ENCRYPTION_KEY' => $this->encodedKey,
            'BLUESKY_CLIENT_PRIVATE_KEY' => $this->privateKey, 'SIGNALIST_PUBLIC_API_URL' => $this->backendUrl,
            'FRONTEND_URL' => $this->frontendUrl, 'NODE_TLS_REJECT_UNAUTHORIZED' => '1',
        ], json_encode($input, JSON_THROW_ON_ERROR), 45);

        try {
            $process->run();
        } catch (Throwable) {
            throw new UnrecoverableMessageHandlingException('Bluesky request failed.');
        }

        if (!$process->isSuccessful() || strlen($process->getOutput()) > 131072) {
            throw new UnrecoverableMessageHandlingException('Bluesky request failed.');
        }
        $data = json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR);

        if (!is_array($data)) {
            throw new UnrecoverableMessageHandlingException('Bluesky response unavailable.');
        }
        $result = [];
        foreach ($data as $key => $value) {
            if (!is_string($key)) {
                throw new UnrecoverableMessageHandlingException('Bluesky response unavailable.');
            } $result[$key] = $value;
        }

        return $result;
    }

    /** @param array<string, mixed> $data */
    private function string(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        if (!is_string($value) || '' === $value) {
            throw new UnrecoverableMessageHandlingException('Bluesky response unavailable.');
        }

return $value;
    }
}
