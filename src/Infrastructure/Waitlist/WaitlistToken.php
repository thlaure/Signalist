<?php

declare(strict_types=1);

namespace App\Infrastructure\Waitlist;

use App\Entity\WaitlistEntry;

final readonly class WaitlistToken
{
    public function __construct(private string $appSecret)
    {
    }

    public function sign(WaitlistEntry $entry, string $action): string
    {
        return hash_hmac('sha256', 'waitlist|'.$entry->getId()->toRfc4122().'|'.$entry->getEmail().'|'.$action, $this->appSecret);
    }

    public function valid(WaitlistEntry $entry, string $action, string $signature): bool
    {
        return hash_equals($this->sign($entry, $action), $signature);
    }
}
