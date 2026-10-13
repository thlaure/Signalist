<?php

declare(strict_types=1);

namespace App\Domain\Connection\Port;

use App\Domain\Connection\DTO\OAuthTokens;

interface OAuthGatewayInterface
{
    public function configured(string $provider): bool;

    public function authorizationUrl(string $provider, string $state, string $challenge): string;

    public function exchange(string $provider, string $code, string $verifier): OAuthTokens;

    public function refresh(string $provider, string $refreshToken, string $remoteId): OAuthTokens;
}
