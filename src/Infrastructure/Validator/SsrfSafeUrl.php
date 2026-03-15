<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SsrfSafeUrl extends Constraint
{
    public string $message = 'The URL "{{ url }}" is not allowed. Only public HTTP/HTTPS URLs are permitted.';
}
