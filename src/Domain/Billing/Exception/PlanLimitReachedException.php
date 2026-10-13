<?php

declare(strict_types=1);

namespace App\Domain\Billing\Exception;

use App\Infrastructure\Exception\ProblemException;

final class PlanLimitReachedException extends ProblemException
{
    public function __construct(string $metric)
    {
        parent::__construct(self::buildTypeUri('plan-limit-reached'), 'Plan limit reached', 402, 'Your plan limit for '.$metric.' has been reached.');
    }
}
