<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class BookmarkNotFoundException extends ProblemException
{
    public function __construct(string $bookmarkId)
    {
        parent::__construct(
            type: self::buildTypeUri('bookmark-not-found'),
            title: 'Bookmark Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The bookmark with ID "%s" was not found.', $bookmarkId),
        );
    }
}
