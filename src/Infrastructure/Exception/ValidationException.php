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
     * @var array<array{field: string, message: string}>
     */
    private array $errors;

    /**
     * @param array<array{field: string, message: string}> $errors
     */
    public function __construct(
        array $errors,
        ?string $instance = null,
    ) {
        $this->errors = $errors;

        parent::__construct(
            type: self::buildTypeUri('validation-error'),
            title: 'Validation Error',
            status: Response::HTTP_BAD_REQUEST,
            detail: 'The request body contains invalid data.',
            instance: $instance,
            extensions: ['errors' => $errors],
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
