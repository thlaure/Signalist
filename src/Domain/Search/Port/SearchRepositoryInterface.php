<?php

declare(strict_types=1);

namespace App\Domain\Search\Port;

use App\Entity\User;

interface SearchRepositoryInterface
{
    /** @return list<string> */
    public function fulltext(User $owner, string $query): array;

    /**
     * @param list<float> $vector
     *
     * @return list<string>
     */
    public function semantic(User $owner, array $vector): array;
}
