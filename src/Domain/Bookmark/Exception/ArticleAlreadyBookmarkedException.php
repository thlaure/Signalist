<?php

declare(strict_types=1);

namespace App\Domain\Bookmark\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class ArticleAlreadyBookmarkedException extends ProblemException
{
    public function __construct(string $articleId)
    {
        parent::__construct(
            type: self::buildTypeUri('article-already-bookmarked'),
            title: 'Article Already Bookmarked',
            status: Response::HTTP_CONFLICT,
            detail: sprintf('The article with ID "%s" is already bookmarked.', $articleId),
        );
    }
}
