<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012001100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Checkpointed Raindrop sync and imported feed sources';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bookmark_sync_run (id UUID NOT NULL, account_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, phase VARCHAR(20) NOT NULL, page INT NOT NULL, processed INT NOT NULL, "offset" INT NOT NULL, include_notes BOOLEAN NOT NULL, remote_account_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5F5EEC3C9B6B5FBA ON bookmark_sync_run (account_id)');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark_sync_run.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE raindrop_link (id UUID NOT NULL, account_id UUID NOT NULL, article_id UUID NOT NULL, local_hash VARCHAR(64) NOT NULL, remote_hash VARCHAR(64) NOT NULL, notes_included BOOLEAN DEFAULT false NOT NULL, remote_account_id VARCHAR(255) NOT NULL, remote_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6115A2B9B6B5FBA ON raindrop_link (account_id)');
        $this->addSql('CREATE INDEX IDX_B6115A2B7294869C ON raindrop_link (article_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B6115A2B9B6B5FBA71DE08C2A3E9C94 ON raindrop_link (account_id, remote_account_id, remote_id)');
        $this->addSql('COMMENT ON COLUMN raindrop_link.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN raindrop_link.account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN raindrop_link.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE bookmark_sync_run ADD CONSTRAINT FK_5F5EEC3C9B6B5FBA FOREIGN KEY (account_id) REFERENCES external_account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE raindrop_link ADD CONSTRAINT FK_B6115A2B9B6B5FBA FOREIGN KEY (account_id) REFERENCES external_account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE raindrop_link ADD CONSTRAINT FK_B6115A2B7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feed ADD source VARCHAR(20) DEFAULT \'rss\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE bookmark_sync_run DROP CONSTRAINT FK_5F5EEC3C9B6B5FBA');
        $this->addSql('ALTER TABLE raindrop_link DROP CONSTRAINT FK_B6115A2B9B6B5FBA');
        $this->addSql('ALTER TABLE raindrop_link DROP CONSTRAINT FK_B6115A2B7294869C');
        $this->addSql('DROP TABLE bookmark_sync_run');
        $this->addSql('DROP TABLE raindrop_link');
        $this->addSql('ALTER TABLE feed DROP source');
    }
}
