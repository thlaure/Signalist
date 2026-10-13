<?php

declare(strict_types=1);

namespace App\Domain\Privacy\MessageHandler;

use App\Domain\Privacy\Message\ApplyRetentionMessage;
use App\Domain\Privacy\Service\RetentionPolicy;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ApplyRetentionMessageHandler
{
    public function __construct(private RetentionPolicy $policy, private bool $enabled = false)
    {
    }

    public function __invoke(ApplyRetentionMessage $message): void
    {
        if ($this->enabled) {
            $this->policy->run(true);
        }
    }
}
