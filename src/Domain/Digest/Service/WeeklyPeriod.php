<?php

declare(strict_types=1);

namespace App\Domain\Digest\Service;

use DateTimeImmutable;
use DateTimeZone;

final readonly class WeeklyPeriod
{
    /** Monday 08:00 UTC, including when a tick is recovered after downtime. */
    public function cutoff(DateTimeImmutable $now): DateTimeImmutable
    {
        $utc = $now->setTimezone(new DateTimeZone('UTC'));
        $monday = $utc->modify('monday this week')->setTime(8, 0);

        return $monday > $utc ? $monday->modify('-1 week') : $monday;
    }
}
