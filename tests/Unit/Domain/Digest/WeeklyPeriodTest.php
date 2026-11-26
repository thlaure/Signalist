<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Digest;

use App\Domain\Digest\Service\WeeklyPeriod;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class WeeklyPeriodTest extends TestCase
{
    public function testScheduleUsesUtcAcrossMondayBoundaryAndTimezones(): void
    {
        $period = new WeeklyPeriod();
        $this->assertSame('2026-10-12 08:00:00 +00:00', $period->cutoff(new DateTimeImmutable('2026-10-19T07:59:59Z'))->format('Y-m-d H:i:s P'));
        $this->assertSame('2026-10-19 08:00:00', $period->cutoff(new DateTimeImmutable('2026-10-19T10:00:00+02:00'))->format('Y-m-d H:i:s'));
        $this->assertSame('2026-10-19', $period->cutoff(new DateTimeImmutable('2026-10-25T23:00:00Z'))->format('Y-m-d'));
    }
}
