<?php

declare(strict_types=1);

namespace App\Domain\Category\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class CategoryHasFeedsException extends ProblemException
{
    public function __construct(string $categoryId)
    {
        parent::__construct(
            type: self::buildTypeUri('category-has-feeds'),
            title: 'Category Has Feeds',
            status: Response::HTTP_CONFLICT,
            detail: sprintf('Cannot delete category "%s" because it has feeds assigned.', $categoryId),
        );
    }
}
