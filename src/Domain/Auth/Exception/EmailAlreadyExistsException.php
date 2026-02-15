<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use App\Infrastructure\Exception\ProblemException;

use function sprintf;

use Symfony\Component\HttpFoundation\Response;

final class EmailAlreadyExistsException extends ProblemException
{
    public function __construct(string $email)
    {
        parent::__construct(
            type: self::buildTypeUri('email-already-exists'),
            title: 'Email Already Exists',
            status: Response::HTTP_CONFLICT,
            detail: sprintf('A user with the email "%s" already exists.', $email),
        );
    }
}
