<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Persist article intelligence independently from source summaries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article ADD ai_summary TEXT DEFAULT NULL');
        $this->addSql("ALTER TABLE article ADD tags JSON DEFAULT '[]' NOT NULL");
        $this->addSql('ALTER TABLE article ADD enriched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article DROP ai_summary');
        $this->addSql('ALTER TABLE article DROP tags');
        $this->addSql('ALTER TABLE article DROP enriched_at');
    }
}
