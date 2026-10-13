<?php

declare(strict_types=1);

namespace App\Infrastructure\Scheduler;

use App\Domain\Billing\Message\DispatchBillingJobsMessage;
use App\Domain\Connection\Message\DispatchConnectionJobsMessage;
use App\Domain\Newsletter\Message\DispatchNewsletterSchedulesMessage;
use App\Domain\Privacy\Message\ApplyRetentionMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('signalist')]
final class SignalistSchedule implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= new Schedule()->with(RecurringMessage::every('1 minute', new DispatchNewsletterSchedulesMessage()), RecurringMessage::every('1 minute', new DispatchBillingJobsMessage()), RecurringMessage::every('1 minute', new DispatchConnectionJobsMessage()), RecurringMessage::every('1 day', new ApplyRetentionMessage()));
    }
}
