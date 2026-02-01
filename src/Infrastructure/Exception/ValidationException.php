<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for validation errors (HTTP 400).
 */
final class ValidationException extends ProblemException
{
    /**
     * @param array<array{field: string, message: string}> $errors
     */
    public function __construct(
        private readonly array $errors,
        ?string $instance = null,
    ) {
        parent::__construct(
            type: self::buildTypeUri('validation-error'),
            title: 'Validation Error',
            status: Response::HTTP_BAD_REQUEST,
            detail: 'The request body contains invalid data.',
            instance: $instance,
            extensions: ['errors' => $this->errors],
        );
    }

    /**
     * @return array<array{field: string, message: string}>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Create a ValidationException from a single field error.
     */
    public static function fromFieldError(string $field, string $message, ?string $instance = null): self
    {
        return new self(
            errors: [['field' => $field, 'message' => $message]],
            instance: $instance,
        );
    }
}
