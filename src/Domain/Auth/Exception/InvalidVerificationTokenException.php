<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use App\Infrastructure\Exception\ProblemException;
use Symfony\Component\HttpFoundation\Response;

final class InvalidVerificationTokenException extends ProblemException
{
    public function __construct()
    {
        parent::__construct(
            type: self::buildTypeUri('invalid-verification-token'),
            title: 'Invalid Verification Token',
            status: Response::HTTP_BAD_REQUEST,
            detail: 'The verification link is invalid or has expired.',
        );
    }
}
