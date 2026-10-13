<?php

declare(strict_types=1);

namespace App\Domain\Search\Service;

use function array_slice;

final readonly class RankFusion
{
    /**
     * @param list<string> $lexical
     * @param list<string> $semantic
     *
     * @return list<string>
     */
    public function combine(array $lexical, array $semantic): array
    {
        $scores = [];
        foreach ([$lexical, $semantic] as $list) {
            foreach ($list as $rank => $id) {
                $scores[$id] = ($scores[$id] ?? 0.0) + 1 / (60 + $rank + 1);
            }
        }
        arsort($scores);

        return array_slice(array_keys($scores), 0, 50);
    }
}
