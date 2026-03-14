<?php

declare(strict_types=1);

namespace App\Infrastructure\Validator;

use function gethostbyname;
use function in_array;
use function ip2long;
use function is_string;
use function parse_url;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SsrfSafeUrlValidator extends ConstraintValidator
{
    /**
     * Private IPv4 ranges blocked to prevent SSRF.
     *
     * @var list<array{string, string}>
     */
    private const array BLOCKED_RANGES = [
        ['0.0.0.0', '0.255.255.255'],
        ['10.0.0.0', '10.255.255.255'],
        ['100.64.0.0', '100.127.255.255'],
        ['127.0.0.0', '127.255.255.255'],
        ['169.254.0.0', '169.254.255.255'],
        ['172.16.0.0', '172.31.255.255'],
        ['192.0.0.0', '192.0.0.255'],
        ['192.168.0.0', '192.168.255.255'],
        ['198.18.0.0', '198.19.255.255'],
        ['198.51.100.0', '198.51.100.255'],
        ['203.0.113.0', '203.0.113.255'],
        ['240.0.0.0', '255.255.255.255'],
    ];

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof SsrfSafeUrl) {
            throw new UnexpectedTypeException($constraint, SsrfSafeUrl::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (!is_string($value)) {
            $this->addViolation($constraint, '');

            return;
        }

        $parsed = parse_url($value);

        if (false === $parsed) {
            $this->addViolation($constraint, $value);

            return;
        }

        $scheme = $parsed['scheme'] ?? '';

        if (!in_array($scheme, ['http', 'https'], true)) {
            $this->addViolation($constraint, $value);

            return;
        }

        $host = $parsed['host'] ?? '';

        if ('' === $host) {
            $this->addViolation($constraint, $value);

            return;
        }

        $ip = gethostbyname($host);

        if ($this->isPrivateOrReserved($ip)) {
            $this->addViolation($constraint, $value);
        }
    }

    private function isPrivateOrReserved(string $ip): bool
    {
        $long = ip2long($ip);

        if (false === $long) {
            return true;
        }

        foreach (self::BLOCKED_RANGES as [$start, $end]) {
            $startLong = ip2long($start);
            $endLong = ip2long($end);

            if (false !== $startLong && false !== $endLong && $long >= $startLong && $long <= $endLong) {
                return true;
            }
        }

        return false;
    }

    private function addViolation(SsrfSafeUrl $constraint, string $value): void
    {
        $this->context->buildViolation($constraint->message)
            ->setParameter('{{ url }}', $value)
            ->addViolation();
    }
}
