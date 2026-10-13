<?php

declare(strict_types=1);

namespace App\Domain\Billing\Service;

final readonly class PlanCatalog
{
    /** @var array<string, array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int}> */
    public const array DEFAULTS = [
        'free' => ['feeds' => 5, 'members' => 1, 'dailyArticles' => 100, 'monthlyAiCalls' => 0],
        'team' => ['feeds' => 30, 'members' => 5, 'dailyArticles' => -1, 'monthlyAiCalls' => 1000],
        'enterprise' => ['feeds' => -1, 'members' => -1, 'dailyArticles' => -1, 'monthlyAiCalls' => 10000],
    ];

    /** @param array<string, array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int}> $limits */
    public function __construct(private array $limits = self::DEFAULTS)
    {
    }

    /** @return array{feeds: int, members: int, dailyArticles: int, monthlyAiCalls: int} */
    public function limits(string $plan): array
    {
        return $this->limits[$plan] ?? $this->limits['free'];
    }
}
