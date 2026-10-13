<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Persistence;

use App\Infrastructure\Persistence\Type\VectorType;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

use const INF;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class VectorTypeTest extends TestCase
{
    public function testRoundTripPreservesAllDimensions(): void
    {
        $type = new VectorType();
        $platform = new PostgreSQLPlatform();
        $vector = array_fill(0, 1536, 0.25);
        $this->assertSame($vector, $type->convertToPHPValue($type->convertToDatabaseValue($vector, $platform), $platform));
    }

    public function testRejectsWrongDimension(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new VectorType()->convertToDatabaseValue([0.25], new PostgreSQLPlatform());
    }

    public function testRejectsNonFiniteValues(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new VectorType()->convertToDatabaseValue(array_fill(0, 1536, INF), new PostgreSQLPlatform());
    }
}
