<?php

declare(strict_types=1);

namespace App\Infrastructure\Exception;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for resource not found errors (HTTP 404).
 */
final class NotFoundException extends ProblemException
{
    public function __construct(
        string $resourceType,
        string $identifier,
        ?string $instance = null,
    ) {
        parent::__construct(
            type: self::buildTypeUri('not-found'),
            title: 'Resource Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The %s with identifier "%s" was not found.', $resourceType, $identifier),
            instance: $instance,
        );
    }
}
