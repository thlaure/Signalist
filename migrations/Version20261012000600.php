<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000600 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Newsletter templates, durable scheduling and delivery outbox';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE newsletter (id UUID NOT NULL PRIMARY KEY, owner_id UUID NOT NULL REFERENCES "user" (id) ON DELETE CASCADE, title VARCHAR(150) NOT NULL, reading_minutes INT NOT NULL, interval_hours INT DEFAULT NULL, next_run_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, html TEXT DEFAULT NULL, generated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        $this->addSql('CREATE TABLE newsletter_run (id UUID NOT NULL PRIMARY KEY, newsletter_id UUID NOT NULL REFERENCES newsletter (id) ON DELETE CASCADE, period VARCHAR(50) NOT NULL, deliver BOOLEAN NOT NULL, status VARCHAR(20) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, UNIQUE (newsletter_id, period))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE newsletter_run');
        $this->addSql('DROP TABLE newsletter');
    }
}
