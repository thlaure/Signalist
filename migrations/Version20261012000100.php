<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Account privacy controls, consent history, and metadata-only audit';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE \"user\" ADD display_name VARCHAR(100) DEFAULT '' NOT NULL");
        $this->addSql('ALTER TABLE "user" ADD processing_restricted BOOLEAN DEFAULT false NOT NULL');
        $this->addSql('CREATE TABLE user_consent (id UUID NOT NULL PRIMARY KEY, user_id UUID NOT NULL REFERENCES "user" (id) ON DELETE CASCADE, purpose VARCHAR(30) NOT NULL, granted BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        $this->addSql('CREATE INDEX idx_consent_user_purpose_date ON user_consent (user_id, purpose, created_at)');
        $this->addSql('CREATE TABLE data_access_audit (id UUID NOT NULL PRIMARY KEY, user_id UUID NOT NULL REFERENCES "user" (id) ON DELETE CASCADE, action VARCHAR(50) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE data_access_audit');
        $this->addSql('DROP TABLE user_consent');
        $this->addSql('ALTER TABLE "user" DROP display_name');
        $this->addSql('ALTER TABLE "user" DROP processing_restricted');
    }
}
