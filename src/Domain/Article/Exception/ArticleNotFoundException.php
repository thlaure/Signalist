<?php

declare(strict_types=1);

namespace App\Domain\Article\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class ArticleNotFoundException extends ProblemException
{
    public function __construct(string $articleId)
    {
        parent::__construct(
            type: self::buildTypeUri('article-not-found'),
            title: 'Article Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The article with ID "%s" was not found.', $articleId),
        );
    }
}
