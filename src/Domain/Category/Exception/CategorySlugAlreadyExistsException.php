<?php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class CategorySlugAlreadyExistsException extends ProblemException
{
    public function __construct(string $slug)
    {
        parent::__construct(
            type: self::buildTypeUri('category-slug-exists'),
            title: 'Category Slug Already Exists',
            status: Response::HTTP_CONFLICT,
            detail: sprintf('A category with slug "%s" already exists.', $slug),
        );
    }
}
