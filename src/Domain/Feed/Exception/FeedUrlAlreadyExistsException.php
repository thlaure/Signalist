<?php

declare(strict_types=1);

namespace App\Domain\Feed\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class FeedUrlAlreadyExistsException extends ProblemException
{
    public function __construct(string $url)
    {
        parent::__construct(
            type: self::buildTypeUri('feed-url-already-exists'),
            title: 'Feed URL Already Exists',
            status: Response::HTTP_CONFLICT,
            detail: sprintf('A feed with URL "%s" already exists.', $url),
        );
    }
}
