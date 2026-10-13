<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Exception;

use App\Infrastructure\Exception\ProblemException;

final class ProcessingRestrictedException extends ProblemException
{
    public function __construct()
    {
        parent::__construct(self::buildTypeUri('processing-restricted'), 'Processing restricted', 403, 'Resume processing in Settings before requesting this operation.');
    }
}
