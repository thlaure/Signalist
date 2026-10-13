<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

use function is_string;

use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class DatabaseTestCase extends KernelTestCase
{
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);

        $database = $this->entityManager->getConnection()->getParams()['dbname'] ?? '';

        if (!is_string($database) || !str_ends_with($database, '_test')) {
            throw new RuntimeException('Integration tests require a disposable _test database.');
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function tearDown(): void
    {
        if (isset($this->entityManager)) {
            $this->entityManager->close();
        }

        parent::tearDown();
    }
}
