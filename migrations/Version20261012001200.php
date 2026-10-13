<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012001200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Social recap drafts and approved crosspost jobs';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookmark_sync_run RENAME COLUMN "offset" TO cursor_offset');
        $this->addSql('CREATE TABLE crosspost (id UUID NOT NULL, account_id UUID NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, published_ids JSON NOT NULL, remote_account_id VARCHAR(255) NOT NULL, posts JSON NOT NULL, record_key VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_586655CF9B6B5FBA ON crosspost (account_id)');
        $this->addSql('COMMENT ON COLUMN crosspost.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN crosspost.account_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN crosspost.approved_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE social_recap (id UUID NOT NULL, owner_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, posts JSON NOT NULL, platform VARCHAR(20) NOT NULL, period_start TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, period_end TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, use_ai BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_652AA9B27E3C61F9 ON social_recap (owner_id)');
        $this->addSql('COMMENT ON COLUMN social_recap.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN social_recap.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN social_recap.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN social_recap.period_start IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN social_recap.period_end IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE crosspost ADD CONSTRAINT FK_586655CF9B6B5FBA FOREIGN KEY (account_id) REFERENCES external_account (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE social_recap ADD CONSTRAINT FK_652AA9B27E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bookmark_sync_run RENAME COLUMN cursor_offset TO "offset"');
        $this->addSql('ALTER TABLE crosspost DROP CONSTRAINT FK_586655CF9B6B5FBA');
        $this->addSql('ALTER TABLE social_recap DROP CONSTRAINT FK_652AA9B27E3C61F9');
        $this->addSql('DROP TABLE crosspost');
        $this->addSql('DROP TABLE social_recap');
    }
}
