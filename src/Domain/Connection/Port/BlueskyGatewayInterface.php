<?php

declare(strict_types=1);

namespace App\Domain\Connection\Port;

use App\Domain\Connection\DTO\OAuthTokens;

interface BlueskyGatewayInterface
{
    public function configured(): bool;

    public function authorize(string $ownerId, string $attemptId, string $handle): string;

    /** @param array<string, string> $parameters */
    public function callback(string $ownerId, string $attemptId, array $parameters): OAuthTokens;

    public function recordKey(): string;

    public function post(string $ownerId, string $did, string $text, string $recordKey, string $createdAt): string;

    public function revoke(string $ownerId, string $did): void;

    /** @return array<string, mixed> */
    public function metadata(bool $keys = false): array;
}
