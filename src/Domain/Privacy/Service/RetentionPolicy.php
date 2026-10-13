<?php

declare(strict_types=1);

namespace App\Domain\Privacy\Service;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;

final readonly class RetentionPolicy
{
    public function __construct(private Connection $connection)
    {
    }

    public function run(bool $apply = false): int
    {
        $where = 'created_at < :cutoff AND NOT EXISTS (SELECT 1 FROM bookmark b WHERE b.article_id = article.id) AND NOT EXISTS (SELECT 1 FROM shared_bookmark b WHERE b.article_id = article.id)';
        $parameters = ['cutoff' => new DateTimeImmutable('-90 days')->format('Y-m-d H:i:s')];
        $value = $this->connection->fetchOne('SELECT COUNT(*) FROM article WHERE '.$where, $parameters);
        $count = is_numeric($value) ? (int) $value : 0;

        if (!$apply) {
            return $count;
        }
        $this->connection->transactional(function () use ($where, $parameters): void {
            $this->connection->executeStatement('DELETE FROM article WHERE '.$where, $parameters);
            $now = ['now' => new DateTimeImmutable()->format('Y-m-d H:i:s')];
            foreach (['oauth_attempt', 'oauth_sdk_store'] as $table) {
                $this->connection->executeStatement('DELETE FROM '.$table.' WHERE expires_at < :now', $now);
            }
            $this->connection->executeStatement('DELETE FROM waitlist_entry WHERE confirmed_at IS NULL AND created_at < :cutoff', ['cutoff' => new DateTimeImmutable('-7 days')->format('Y-m-d H:i:s')]);
            $cutoff = ['cutoff' => new DateTimeImmutable('-30 days')->format('Y-m-d H:i:s')];
            $this->connection->executeStatement('DELETE FROM data_access_audit WHERE created_at < :cutoff', $cutoff);
            $this->connection->executeStatement('DELETE FROM "user" WHERE deleted_at < :cutoff', $cutoff);
        });

        return $count;
    }
}
