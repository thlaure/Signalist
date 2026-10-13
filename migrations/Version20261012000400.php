<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Double opt-in waitlist storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE waitlist_entry (id UUID NOT NULL PRIMARY KEY, email VARCHAR(180) NOT NULL UNIQUE, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, confirmed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE waitlist_entry');
    }
}
