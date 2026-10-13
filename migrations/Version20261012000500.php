<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Asynchronous search jobs and full-text index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE article_search (id UUID NOT NULL PRIMARY KEY, owner_id UUID NOT NULL REFERENCES "user" (id) ON DELETE CASCADE, query VARCHAR(500) NOT NULL, mode VARCHAR(20) NOT NULL, status VARCHAR(20) NOT NULL, article_ids JSON NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL)');
        $this->addSql("CREATE INDEX idx_article_fulltext ON article USING gin (to_tsvector('simple', title || ' ' || COALESCE(summary, '') || ' ' || COALESCE(ai_summary, '')))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_article_fulltext');
        $this->addSql('DROP TABLE article_search');
    }
}
