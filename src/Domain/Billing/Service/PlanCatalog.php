<?php

declare(strict_types=1);

namespace App\Domain\Billing\Service;

use InvalidArgumentException;

final readonly class PlanCatalog
{
    /** @var array<string, array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int}> */
    public const array DEFAULTS = [
        'free' => ['feeds' => 3, 'members' => 1, 'dailyArticles' => 100, 'monthlyAiCalls' => 0],
        'team' => ['feeds' => 20, 'members' => 5, 'dailyArticles' => 100, 'monthlyAiCalls' => 1000],
        'enterprise' => ['feeds' => 20, 'members' => -1, 'dailyArticles' => 100, 'monthlyAiCalls' => 10000],
    ];

    /** @param array<string, array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int}> $limits */
    public function __construct(private array $limits = self::DEFAULTS, private ?int $freeSources = null, private ?int $paidSources = null)
    {
    }

    /** @return array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int} */
    public function limits(string $plan): array
    {
        $limits = $this->limits[$plan] ?? $this->limits['free'];
        $override = 'free' === $plan ? $this->freeSources : $this->paidSources;

        if (null !== $override) {
            if ($override < 1 || $override > 20) {
                throw new InvalidArgumentException('Source limits must be between 1 and 20.');
            }
            $limits['feeds'] = $override;
        }

        return $limits;
    }
}
