<?php

declare(strict_types=1);

namespace App\Infrastructure\Connection;

use App\Domain\Connection\DTO\OAuthTokens;
use App\Domain\Connection\Port\OAuthGatewayInterface;
use App\Infrastructure\External\BoundedJsonClient;
use DateTimeImmutable;

use function is_array;
use function is_int;
use function is_string;

use const PHP_QUERY_RFC3986;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class ProviderOAuthGateway implements OAuthGatewayInterface
{
    private const array ENDPOINTS = [
        'raindrop' => ['authorize' => 'https://raindrop.io/oauth/authorize', 'token' => 'https://raindrop.io/oauth/access_token', 'profile' => 'https://api.raindrop.io/rest/v1/user', 'scope' => ''],
        'linkedin' => ['authorize' => 'https://www.linkedin.com/oauth/v2/authorization', 'token' => 'https://www.linkedin.com/oauth/v2/accessToken', 'profile' => 'https://api.linkedin.com/v2/userinfo', 'scope' => 'openid profile w_member_social'],
        'x' => ['authorize' => 'https://x.com/i/oauth2/authorize', 'token' => 'https://api.x.com/2/oauth2/token', 'profile' => 'https://api.x.com/2/users/me', 'scope' => 'tweet.read tweet.write users.read offline.access'],
    ];

    /** @param array<string, array{clientId: string, clientSecret: string}> $credentials */
    public function __construct(private BoundedJsonClient $http, private array $credentials, private string $backendUrl)
    {
    }

    public function configured(string $provider): bool
    {
        return isset(self::ENDPOINTS[$provider], $this->credentials[$provider]) && '' !== $this->credentials[$provider]['clientId'] && '' !== $this->credentials[$provider]['clientSecret'];
    }

    public function authorizationUrl(string $provider, string $state, string $challenge): string
    {
        $endpoint = $this->endpoint($provider);
        $credential = $this->credentials[$provider];
        $query = ['response_type' => 'code', 'client_id' => $credential['clientId'], 'redirect_uri' => $this->redirect($provider), 'state' => $state];

        if ('' !== $endpoint['scope']) {
            $query['scope'] = $endpoint['scope'];
        }

        if ('x' === $provider) {
            $query['code_challenge'] = $challenge;
            $query['code_challenge_method'] = 'S256';
        }

        return $endpoint['authorize'].'?'.http_build_query($query, encoding_type: PHP_QUERY_RFC3986);
    }

    public function exchange(string $provider, string $code, string $verifier): OAuthTokens
    {
        $parameters = ['grant_type' => 'authorization_code', 'code' => $code, 'redirect_uri' => $this->redirect($provider)];

        if ('x' === $provider) {
            $parameters['code_verifier'] = $verifier;
        }
        $data = $this->token($provider, $parameters);
        $accessToken = $data['access_token'] ?? null;

        if (!is_string($accessToken)) {
            throw new UnrecoverableMessageHandlingException('Account authorization failed.');
        }
        $profile = $this->json('GET', $this->endpoint($provider)['profile'], ['auth_bearer' => $accessToken]);
        $remoteId = match ($provider) {
            'linkedin' => $profile['sub'] ?? null, 'x' => is_array($profile['data'] ?? null) ? $profile['data']['id'] ?? null : null, default => is_array($profile['user'] ?? null) ? $profile['user']['_id'] ?? null : null
        };

        if (!is_string($remoteId) && !is_int($remoteId)) {
            throw new UnrecoverableMessageHandlingException('Account identity unavailable.');
        }

        return $this->tokens($data, (string) $remoteId);
    }

    public function refresh(string $provider, string $refreshToken, string $remoteId): OAuthTokens
    {
        return $this->tokens($this->token($provider, ['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]), $remoteId);
    }

    /**
     * @param array<string, string> $parameters
     *
     * @return array<string, mixed>
     */
    private function token(string $provider, array $parameters): array
    {
        $endpoint = $this->endpoint($provider);
        $credential = $this->credentials[$provider];

        if ('x' === $provider) {
            return $this->json('POST', $endpoint['token'], ['auth_basic' => [$credential['clientId'], $credential['clientSecret']], 'body' => $parameters]);
        }
        $parameters['client_id'] = $credential['clientId'];
        $parameters['client_secret'] = $credential['clientSecret'];

        return $this->json('POST', $endpoint['token'], 'raindrop' === $provider ? ['json' => $parameters] : ['body' => $parameters]);
    }

    /** @param array<string, mixed> $data */
    private function tokens(array $data, string $remoteId): OAuthTokens
    {
        $access = $data['access_token'] ?? null;
        $refresh = $data['refresh_token'] ?? null;
        $expires = $data['expires_in'] ?? null;

        if (!is_string($access) || '' === $access || !is_int($expires) || $expires < 1 || $expires > 31536000) {
            throw new UnrecoverableMessageHandlingException('Account authorization failed.');
        }

        return new OAuthTokens($access, is_string($refresh) ? $refresh : null, new DateTimeImmutable()->modify('+'.$expires.' seconds'), $remoteId);
    }

    /** @return array{authorize: string, token: string, profile: string, scope: string} */
    private function endpoint(string $provider): array
    {
        if (!$this->configured($provider)) {
            throw new UnrecoverableMessageHandlingException('Account provider setup is pending.');
        }

        return self::ENDPOINTS[$provider];
    }

    private function redirect(string $provider): string
    {
        return rtrim($this->backendUrl, '/').'/api/v1/connections/'.$provider.'/callback';
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    private function json(string $method, string $url, array $options): array
    {
        return $this->http->request($method, $url, $options);
    }
}
