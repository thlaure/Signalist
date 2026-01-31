<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for conflict errors (HTTP 409).
 */
final class ConflictException extends ProblemException
{
    public function __construct(
        string $resourceType,
        string $conflictField,
        string $conflictValue,
        ?string $instance = null,
    ) {
        parent::__construct(
            type: self::buildTypeUri('conflict'),
            title: 'Resource Conflict',
            status: Response::HTTP_CONFLICT,
            detail: sprintf(
                'A %s with %s "%s" already exists.',
                $resourceType,
                $conflictField,
                $conflictValue,
            ),
            instance: $instance,
            extensions: [
                'conflictField' => $conflictField,
                'conflictValue' => $conflictValue,
            ],
        );
    }
}
