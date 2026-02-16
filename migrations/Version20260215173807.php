<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260215173807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add owner_id to category and feed tables with per-user unique constraints';
    }

    public function up(Schema $schema): void
    {
        // Add owner_id as nullable first, backfill existing rows, then set NOT NULL
        $this->addSql('DROP INDEX uniq_category_slug');
        $this->addSql('ALTER TABLE category ADD owner_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN category.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('UPDATE category SET owner_id = (SELECT id FROM "user" LIMIT 1) WHERE owner_id IS NULL');
        $this->addSql('ALTER TABLE category ALTER COLUMN owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C17E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_64C19C17E3C61F9 ON category (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug_owner ON category (slug, owner_id)');
        $this->addSql('DROP INDEX uniq_feed_url');
        $this->addSql('ALTER TABLE feed ADD owner_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN feed.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('UPDATE feed SET owner_id = (SELECT id FROM "user" LIMIT 1) WHERE owner_id IS NULL');
        $this->addSql('ALTER TABLE feed ALTER COLUMN owner_id SET NOT NULL');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044AB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_234044AB7E3C61F9 ON feed (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url_owner ON feed (url, owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE feed DROP CONSTRAINT FK_234044AB7E3C61F9');
        $this->addSql('DROP INDEX IDX_234044AB7E3C61F9');
        $this->addSql('DROP INDEX uniq_feed_url_owner');
        $this->addSql('ALTER TABLE feed DROP owner_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url ON feed (url)');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C17E3C61F9');
        $this->addSql('DROP INDEX IDX_64C19C17E3C61F9');
        $this->addSql('DROP INDEX uniq_category_slug_owner');
        $this->addSql('ALTER TABLE category DROP owner_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug ON category (slug)');
    }
}
