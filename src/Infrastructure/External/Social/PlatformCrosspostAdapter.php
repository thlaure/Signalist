<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Social;

use App\Domain\Connection\Port\BlueskyGatewayInterface;
use App\Domain\Connection\Service\AccountTokens;
use App\Domain\Social\Port\CrosspostAdapterInterface;
use App\Entity\Crosspost;
use App\Infrastructure\External\BoundedJsonClient;

use const DATE_ATOM;

use function is_array;
use function is_string;
use function strlen;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Uid\Uuid;

final readonly class PlatformCrosspostAdapter implements CrosspostAdapterInterface
{
    public function __construct(private BoundedJsonClient $http, private AccountTokens $tokens, private BlueskyGatewayInterface $bluesky, private string $linkedinVersion)
    {
    }

    public function recordKey(string $platform): string
    {
        return 'bluesky' === $platform ? $this->bluesky->recordKey() : Uuid::v7()->toRfc4122();
    }

    public function publish(Crosspost $post, int $index): string
    {
        $account = $post->getAccount();
        $text = $post->getPosts()[$index];

        if ('bluesky' === $account->getProvider()) {
            return $this->bluesky->post($account->getOwner()->getId()->toRfc4122(), $post->getRemoteAccountId(), $text, $post->getRecordKey(), $post->getApprovedAt()->format(DATE_ATOM));
        }
        $token = $this->tokens->accessToken($account, $post->getRemoteAccountId());

        if ('x' === $account->getProvider()) {
            $payload = ['text' => $text];

            if ($index > 0) {
                $payload['reply'] = ['in_reply_to_tweet_id' => $post->getPublishedIds()[$index - 1]];
            }
            $response = $this->http->request('POST', 'https://api.x.com/2/tweets', ['auth_bearer' => $token, 'json' => $payload]);
            $data = $response['data'] ?? null;
            $id = is_array($data) ? ($data['id'] ?? null) : null;
        } elseif ('linkedin' === $account->getProvider()) {
            if (!preg_match('/^\d{6}$/', $this->linkedinVersion)) {
                throw new UnrecoverableMessageHandlingException('LinkedIn API version is invalid.');
            }
            $response = $this->http->requestWithHeaders('POST', 'https://api.linkedin.com/rest/posts', [
                'auth_bearer' => $token,
                'headers' => ['LinkedIn-Version' => $this->linkedinVersion, 'X-Restli-Protocol-Version' => '2.0.0'],
                'json' => ['author' => 'urn:li:person:'.$post->getRemoteAccountId(), 'commentary' => $text, 'visibility' => 'PUBLIC', 'distribution' => ['feedDistribution' => 'MAIN_FEED', 'targetEntities' => [], 'thirdPartyDistributionChannels' => []], 'lifecycleState' => 'PUBLISHED', 'isReshareDisabledByAuthor' => false],
            ], true);
            $id = $response['headers']['x-restli-id'][0] ?? null;
        } else {
            throw new UnrecoverableMessageHandlingException('Unsupported social platform.');
        }

        if (!is_string($id) || '' === $id || strlen($id) > 1024) {
            throw new UnrecoverableMessageHandlingException('Provider did not return a publication identifier. Check the remote account before retrying.');
        }

        return $id;
    }
}
