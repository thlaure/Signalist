<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use App\Infrastructure\Exception\ProblemException;
use Symfony\Component\HttpFoundation\Response;

final class EmailNotVerifiedException extends ProblemException
{
    public function __construct()
    {
        parent::__construct(
            type: self::buildTypeUri('email-not-verified'),
            title: 'Email Not Verified',
            status: Response::HTTP_FORBIDDEN,
            detail: 'You must verify your email address before logging in.',
        );
    }
}
