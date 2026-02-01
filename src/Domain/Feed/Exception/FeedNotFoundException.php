<?php

declare(strict_types=1);

namespace App\Domain\Feed\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class FeedNotFoundException extends ProblemException
{
    public function __construct(string $feedId)
    {
        parent::__construct(
            type: self::buildTypeUri('feed-not-found'),
            title: 'Feed Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The feed with ID "%s" was not found.', $feedId),
        );
    }
}
