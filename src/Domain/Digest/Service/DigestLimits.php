<?php

declare(strict_types=1);

namespace App\Domain\Digest\Service;

use InvalidArgumentException;

final readonly class DigestLimits
{
    public function __construct(public int $articlesPerSource = 2, public int $articlesPerDigest = 8, public int $charactersPerArticle = 2000, public int $attemptsPerDigest = 3)
    {
        if ($articlesPerSource < 1 || $articlesPerSource > 5 || $articlesPerDigest < 1 || $articlesPerDigest > 20 || $charactersPerArticle < 100 || $charactersPerArticle > 4000 || $attemptsPerDigest < 1 || $attemptsPerDigest > 3 || $articlesPerDigest * $charactersPerArticle > 20000) {
            throw new InvalidArgumentException('Digest limits exceed the supported processing budget.');
        }
    }
}
