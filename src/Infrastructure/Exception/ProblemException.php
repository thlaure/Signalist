<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use Exception;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Base exception class implementing RFC 7807 - Problem Details for HTTP APIs.
 *
 * @see https://www.rfc-editor.org/rfc/rfc7807
 */
abstract class ProblemException extends Exception implements HttpExceptionInterface
{
    private const string BASE_TYPE_URI = 'https://signalist.app/problems';

    /**
     * @param array<string, mixed> $extensions Additional problem details
     */
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly int $status,
        string $detail,
        public readonly ?string $instance = null,
        public readonly array $extensions = [],
    ) {
        parent::__construct($detail, $status);
    }

    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * @return array<string, int>
     */
    public function getHeaders(): array
    {
        return [];
    }

    public function getDetail(): string
    {
        return $this->getMessage();
    }

    /**
     * @return array<string, mixed>
     */
    public function toProblemDetails(): array
    {
        return array_filter([
            'type' => $this->type,
            'title' => $this->title,
            'status' => $this->status,
            'detail' => $this->getDetail(),
            'instance' => $this->instance,
            ...$this->extensions,
        ], static fn (mixed $value): bool => $value !== null);
    }

    protected static function buildTypeUri(string $problemType): string
    {
        return self::BASE_TYPE_URI . '/' . $problemType;
    }
}
