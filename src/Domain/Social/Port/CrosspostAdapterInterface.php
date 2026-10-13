<?php

declare(strict_types=1);

namespace App\Domain\Social\Port;

use App\Entity\Crosspost;

interface CrosspostAdapterInterface
{
    public function recordKey(string $platform): string;

    public function publish(Crosspost $post, int $index): string;
}
