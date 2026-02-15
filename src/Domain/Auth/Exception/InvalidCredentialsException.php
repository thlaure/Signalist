<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use App\Infrastructure\Exception\ProblemException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidCredentialsException extends ProblemException
{
    public function __construct()
    {
        parent::__construct(
            type: self::buildTypeUri('invalid-credentials'),
            title: 'Invalid Credentials',
            status: Response::HTTP_UNAUTHORIZED,
            detail: 'The provided credentials are invalid.',
        );
    }
}
