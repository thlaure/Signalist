<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Exception;

use App\Infrastructure\Exception\ProblemException;

final class ConsentRequiredException extends ProblemException
{
    public function __construct(string $purpose)
    {
        parent::__construct(self::buildTypeUri('consent-required'), 'Consent required', 403, 'Explicit consent for '.$purpose.' is required.');
    }
}
