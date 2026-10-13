<?php

declare(strict_types=1);

namespace App\Domain\Sync\DTO;

final readonly class RemoteBookmark
{
    public function __construct(public string $id, public string $url, public string $title, public ?string $note)
    {
    }
}
