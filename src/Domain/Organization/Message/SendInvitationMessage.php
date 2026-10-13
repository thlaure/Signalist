<?php

declare(strict_types=1);

namespace App\Domain\Organization\Message;

final readonly class SendInvitationMessage
{
    public function __construct(public string $invitationId)
    {
    }
}
