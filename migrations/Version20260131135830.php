<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260131135830 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id UUID NOT NULL, feed_id UUID NOT NULL, guid VARCHAR(512) NOT NULL, title VARCHAR(500) NOT NULL, url VARCHAR(2048) NOT NULL, summary TEXT DEFAULT NULL, content TEXT DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, image_url VARCHAR(2048) DEFAULT NULL, is_read BOOLEAN DEFAULT false NOT NULL, published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_23A0E6651A5BC03 ON article (feed_id)');
        $this->addSql('CREATE INDEX idx_article_published_at ON article (published_at)');
        $this->addSql('CREATE INDEX idx_article_is_read ON article (is_read)');
        $this->addSql('CREATE UNIQUE INDEX uniq_article_feed_guid ON article (feed_id, guid)');
        $this->addSql('COMMENT ON COLUMN article.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article.feed_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article.published_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN article.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE article_embedding (id UUID NOT NULL, article_id UUID NOT NULL, embedding JSON DEFAULT NULL, chunk_index INT DEFAULT 0 NOT NULL, chunk_text TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_article_embedding_article ON article_embedding (article_id)');
        $this->addSql('COMMENT ON COLUMN article_embedding.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_embedding.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_embedding.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE bookmark (id UUID NOT NULL, article_id UUID NOT NULL, notes TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_bookmark_article ON bookmark (article_id)');
        $this->addSql('COMMENT ON COLUMN bookmark.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE category (id UUID NOT NULL, name VARCHAR(100) NOT NULL, slug VARCHAR(100) NOT NULL, description TEXT DEFAULT NULL, color VARCHAR(7) DEFAULT NULL, position INT DEFAULT 0 NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug ON category (slug)');
        $this->addSql('COMMENT ON COLUMN category.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN category.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN category.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE feed (id UUID NOT NULL, category_id UUID NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(2048) NOT NULL, status VARCHAR(20) DEFAULT \'active\' NOT NULL, last_error TEXT DEFAULT NULL, last_fetched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_234044AB12469DE2 ON feed (category_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url ON feed (url)');
        $this->addSql('COMMENT ON COLUMN feed.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN feed.category_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN feed.last_fetched_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN feed.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN feed.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6651A5BC03 FOREIGN KEY (feed_id) REFERENCES feed (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_embedding ADD CONSTRAINT FK_926E058A7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookmark ADD CONSTRAINT FK_DA62921D7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044AB12469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE article DROP CONSTRAINT FK_23A0E6651A5BC03');
        $this->addSql('ALTER TABLE article_embedding DROP CONSTRAINT FK_926E058A7294869C');
        $this->addSql('ALTER TABLE bookmark DROP CONSTRAINT FK_DA62921D7294869C');
        $this->addSql('ALTER TABLE feed DROP CONSTRAINT FK_234044AB12469DE2');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE article_embedding');
        $this->addSql('DROP TABLE bookmark');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE feed');
    }
}
