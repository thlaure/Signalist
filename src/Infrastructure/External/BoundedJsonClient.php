<?php

declare(strict_types=1);

namespace App\Infrastructure\External;

use function is_array;
use function is_string;

use const JSON_THROW_ON_ERROR;

use RuntimeException;

use function strlen;

use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

final readonly class BoundedJsonClient
{
    public function __construct(private HttpClientInterface $http)
    {
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function request(string $method, string $url, array $options = []): array
    {
        return $this->requestWithHeaders($method, $url, $options)['data'];
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array{data: array<string, mixed>, headers: array<string, list<string>>}
     */
    public function requestWithHeaders(string $method, string $url, array $options = [], bool $allowEmpty = false): array
    {
        $response = null;

        try {
            $response = $this->http->request($method, $url, $options + ['timeout' => 10, 'max_duration' => 15, 'max_redirects' => 0, 'buffer' => false]);
            $body = '';
            foreach ($this->http->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    throw new RuntimeException('Provider request timed out.');
                }
                $body .= $chunk->getContent();

                if (strlen($body) > 1048576) {
                    throw new UnrecoverableMessageHandlingException('Provider response exceeds size limit.');
                }
            }
            $status = $response->getStatusCode();

            if (429 === $status || $status >= 500) {
                throw new RuntimeException('Provider temporarily unavailable.');
            }

            if ($status < 200 || $status >= 300) {
                throw new UnrecoverableMessageHandlingException('Provider rejected the request. Reconnect if authorization expired.');
            }
            $data = $allowEmpty && '' === trim($body) ? [] : json_decode($body, true, flags: JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                throw new UnrecoverableMessageHandlingException('Provider returned an invalid response.');
            }
            $object = [];
            foreach ($data as $key => $value) {
                if (!is_string($key)) {
                    throw new UnrecoverableMessageHandlingException('Provider returned an invalid object.');
                }
                $object[$key] = $value;
            }

            return ['data' => $object, 'headers' => $response->getHeaders(false)];
        } catch (UnrecoverableMessageHandlingException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RuntimeException('Provider request failed.');
        } finally {
            $response?->cancel();
        }
    }
}
