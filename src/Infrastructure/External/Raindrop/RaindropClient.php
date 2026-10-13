<?php

declare(strict_types=1);

namespace App\Infrastructure\External\Raindrop;

use App\Domain\Sync\DTO\RemoteBookmark;
use App\Domain\Sync\Port\RaindropClientInterface;
use App\Infrastructure\External\BoundedJsonClient;

use function is_array;
use function is_int;
use function is_string;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

final readonly class RaindropClient implements RaindropClientInterface
{
    public function __construct(private BoundedJsonClient $http)
    {
    }

    public function page(string $accessToken, int $page): array
    {
        $data = $this->http->request('GET', 'https://api.raindrop.io/rest/v1/raindrops/0', ['auth_bearer' => $accessToken, 'query' => ['page' => $page, 'perpage' => 50, 'sort' => 'created']]);
        $items = $data['items'] ?? null;

        if (!is_array($items)) {
            throw new UnrecoverableMessageHandlingException('Invalid bookmark response.');
        }
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new UnrecoverableMessageHandlingException('Invalid bookmark response.');
            } $result[] = $this->bookmark($item);
        }

        return $result;
    }

    public function create(string $accessToken, string $url, string $title, ?string $note): RemoteBookmark
    {
        return $this->write('POST', 'https://api.raindrop.io/rest/v1/raindrop', $accessToken, $url, $title, $note);
    }

    public function update(string $accessToken, string $id, string $url, string $title, ?string $note): RemoteBookmark
    {
        if (!ctype_digit($id)) {
            throw new UnrecoverableMessageHandlingException('Invalid bookmark identifier.');
        }

        return $this->write('PUT', 'https://api.raindrop.io/rest/v1/raindrop/'.$id, $accessToken, $url, $title, $note);
    }

    /** @param array<mixed> $item */
    private function bookmark(array $item): RemoteBookmark
    {
        $id = $item['_id'] ?? null;
        $url = $item['link'] ?? null;
        $title = $item['title'] ?? null;
        $note = $item['note'] ?? null;

        if ((!is_int($id) && !is_string($id)) || !ctype_digit((string) $id) || !is_string($url) || !is_string($title)) {
            throw new UnrecoverableMessageHandlingException('Invalid bookmark response.');
        }

        return new RemoteBookmark((string) $id, $url, $title, is_string($note) ? $note : null);
    }

    private function write(string $method, string $endpoint, string $token, string $url, string $title, ?string $note): RemoteBookmark
    {
        $payload = ['link' => $url, 'title' => $title];

        if (null !== $note) {
            $payload['note'] = $note;
        }
        $response = $this->http->request($method, $endpoint, ['auth_bearer' => $token, 'json' => $payload]);

        if (!is_array($response['item'] ?? null)) {
            throw new UnrecoverableMessageHandlingException('Invalid bookmark response.');
        }

        return $this->bookmark($response['item']);
    }
}
