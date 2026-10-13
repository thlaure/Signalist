<?php

declare(strict_types=1);

namespace App\Domain\Billing\Service;

use App\Domain\Billing\Exception\PlanLimitReachedException;
use App\Entity\Organization;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Symfony\Component\Uid\Uuid;

final readonly class UsageBudget
{
    public function __construct(private Connection $connection, private PlanCatalog $plans)
    {
    }

    public function aiCalls(User $owner, int $count, ?Organization $organization = null): void
    {
        $limit = $this->plans->limits($organization?->getPlan() ?? $owner->getPlan())['monthlyAiCalls'];

        if (0 === $limit) {
            throw new PlanLimitReachedException('AI calls');
        }
        $this->consume($organization instanceof Organization ? 'organization-'.$organization->getId()->toRfc4122() : 'user-'.$owner->getId()->toRfc4122(), 'ai_calls', new DateTimeImmutable()->format('Y-m'), $limit, $count);
    }

    public function article(User $owner, ?Organization $organization = null): bool
    {
        $limit = $this->plans->limits($organization?->getPlan() ?? $owner->getPlan())['dailyArticles'];

        try {
            $this->consume($organization instanceof Organization ? 'organization-'.$organization->getId()->toRfc4122() : 'user-'.$owner->getId()->toRfc4122(), 'articles', new DateTimeImmutable()->format('Y-m-d'), $limit, 1);

            return true;
        } catch (PlanLimitReachedException) {
            return false;
        }
    }

    private function consume(string $scope, string $metric, string $period, int $limit, int $count): void
    {
        if ($count < 1) {
            return;
        }

        if ($limit < 0) {
            return;
        }

        if ($count > $limit) {
            throw new PlanLimitReachedException($metric);
        }
        $used = $this->connection->fetchOne(<<<'SQL'
            INSERT INTO usage_counter (id, scope_key, metric, period, used) VALUES (:id, :scope, :metric, :period, :count)
            ON CONFLICT (scope_key, metric, period) DO UPDATE SET used = usage_counter.used + :count WHERE usage_counter.used + :count <= :budget
            RETURNING used
            SQL, ['id' => Uuid::v7()->toRfc4122(), 'scope' => $scope, 'metric' => $metric, 'period' => $period, 'count' => $count, 'budget' => $limit]);

        if (false === $used) {
            throw new PlanLimitReachedException($metric);
        }
    }
}
