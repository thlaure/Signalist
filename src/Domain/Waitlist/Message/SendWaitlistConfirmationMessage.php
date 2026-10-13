<?php

declare(strict_types=1);

namespace App\Domain\Waitlist\Message;

final readonly class SendWaitlistConfirmationMessage
{
    public function __construct(public string $entryId)
    {
    }
}
