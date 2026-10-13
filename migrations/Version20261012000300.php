<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261012000300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert existing embedding JSON to checked 1536-dimensional pgvector storage';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE EXTENSION IF NOT EXISTS vector');
        // PostgreSQL rejects invalid dimensions; a failed conversion leaves the old column intact.
        $this->addSql('ALTER TABLE article_embedding ALTER embedding TYPE vector(1536) USING embedding::text::vector(1536)');
        $this->addSql("COMMENT ON COLUMN article_embedding.embedding IS '(DC2Type:vector)'");
        $this->addSql('CREATE INDEX idx_embedding_cosine ON article_embedding USING ivfflat (embedding vector_cosine_ops) WITH (lists = 100)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_embedding_cosine');
        $this->addSql('ALTER TABLE article_embedding ALTER embedding TYPE JSON USING embedding::text::json');
        $this->addSql('COMMENT ON COLUMN article_embedding.embedding IS NULL');
    }
}
