<?php

declare(strict_types=1);

namespace App\Infrastructure\Organization;

use App\Entity\OrganizationInvitation;

final readonly class InvitationToken
{
    public function __construct(private string $appSecret, private string $frontendUrl)
    {
    }

    public function sign(OrganizationInvitation $invitation): string
    {
        return hash_hmac('sha256', 'invitation|'.$invitation->getId()->toRfc4122().'|'.$invitation->getEmail().'|'.$invitation->getRole().'|'.$invitation->getExpiresAt()->getTimestamp(), $this->appSecret);
    }

    public function url(OrganizationInvitation $invitation): string
    {
        return rtrim($this->frontendUrl, '/').'/invitations?id='.$invitation->getId()->toRfc4122().'&signature='.$this->sign($invitation);
    }
}
