<?php

declare(strict_types=1);

namespace App\Tests\Integration;

final class DatabaseResetTest extends DatabaseTestCase
{
    public function testResetRemovesForeignKeysOutsideOrmMetadata(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('CREATE TABLE reset_probe (owner_id UUID REFERENCES "user" (id))');

        $this->resetDatabaseSchema();

        self::assertNull($connection->fetchOne("SELECT to_regclass('reset_probe')"));
        self::assertSame(0, $connection->fetchOne('SELECT COUNT(*) FROM "user"'));

        $this->resetDatabaseSchema();
        self::assertSame(0, $connection->fetchOne('SELECT COUNT(*) FROM "user"'));
    }
}
