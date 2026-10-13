<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Durable seat synchronization outbox';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE seat_sync_request (id UUID NOT NULL PRIMARY KEY, subscription_id UUID NOT NULL REFERENCES subscription (id) ON DELETE CASCADE, processed BOOLEAN NOT NULL)');
        $this->addSql("COMMENT ON COLUMN seat_sync_request.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN seat_sync_request.subscription_id IS '(DC2Type:uuid)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE seat_sync_request');
    }
}
