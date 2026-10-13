<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Type;

use function count;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use InvalidArgumentException;

use function is_array;
use function is_float;
use function is_int;
use function is_string;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_THROW_ON_ERROR;

final class VectorType extends Type
{
    public const string NAME = 'vector';

    public const int DIMENSIONS = 1536;

    /** @param array<string, mixed> $column */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'vector(1536)';
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (!is_array($value) || !array_is_list($value) || self::DIMENSIONS !== count($value)) {
            throw new InvalidArgumentException('Expected a 1536-dimensional vector.');
        }
        foreach ($value as $number) {
            if ((!is_int($number) && !is_float($number)) || !is_finite((float) $number)) {
                throw new InvalidArgumentException('Vector values must be finite numbers.');
            }
        }

        return json_encode($value, JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION);
    }

    /** @return list<float>|null */
    public function convertToPHPValue(mixed $value, AbstractPlatform $platform): ?array
    {
        if (null === $value) {
            return null;
        }

        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid stored vector.');
        }
        $decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
        $this->convertToDatabaseValue($decoded, $platform);

        if (!is_array($decoded)) {
            throw new InvalidArgumentException('Invalid stored vector.');
        }
        $numbers = [];
        foreach ($decoded as $number) {
            if (!is_int($number) && !is_float($number)) {
                throw new InvalidArgumentException('Invalid stored vector.');
            }
            $numbers[] = (float) $number;
        }

        return $numbers;
    }
}
