<?php

declare(strict_types=1);

namespace App\Domain\Connection\DTO;

use DateTimeImmutable;

use const JSON_THROW_ON_ERROR;

final readonly class OAuthTokens
{
    public function __construct(public string $accessToken, public ?string $refreshToken, public DateTimeImmutable $expiresAt, public string $remoteId)
    {
    }

    public function serialize(): string
    {
        return json_encode(['accessToken' => $this->accessToken, 'refreshToken' => $this->refreshToken], JSON_THROW_ON_ERROR);
    }
}
