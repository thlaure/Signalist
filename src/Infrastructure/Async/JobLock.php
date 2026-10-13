<?php

declare(strict_types=1);

namespace App\Infrastructure\Async;

use Closure;
use Doctrine\DBAL\Connection;

use function in_array;

/** Serialize repeated outbox deliveries before external inference. */
final readonly class JobLock
{
    public function __construct(private Connection $connection)
    {
    }

    public function run(string $key, Closure $work): void
    {
        if (!in_array($this->connection->fetchOne('SELECT pg_try_advisory_lock(hashtextextended(:key, 0))', ['key' => $key]), [true, 1, 't'], true)) {
            return;
        }

        try {
            $work();
        } finally {
            $this->connection->executeQuery('SELECT pg_advisory_unlock(hashtextextended(:key, 0))', ['key' => $key]);
        }
    }
}
