<?php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class CategoryNotFoundException extends ProblemException
{
    public function __construct(string $categoryId)
    {
        parent::__construct(
            type: self::buildTypeUri('category-not-found'),
            title: 'Category Not Found',
            status: Response::HTTP_NOT_FOUND,
            detail: sprintf('The category with ID "%s" was not found.', $categoryId),
        );
    }
}
