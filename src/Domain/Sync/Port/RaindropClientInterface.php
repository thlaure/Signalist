<?php

declare(strict_types=1);

namespace App\Domain\Sync\Port;

use App\Domain\Sync\DTO\RemoteBookmark;

interface RaindropClientInterface
{
    /** @return list<RemoteBookmark> */
    public function page(string $accessToken, int $page): array;

    public function create(string $accessToken, string $url, string $title, ?string $note): RemoteBookmark;

    public function update(string $accessToken, string $id, string $url, string $title, ?string $note): RemoteBookmark;
}
