<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012001000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Encrypted external credentials, browser-bound authorization and scoped Bluesky SDK state';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE external_account (id UUID NOT NULL, owner_id UUID NOT NULL, encrypted_tokens TEXT DEFAULT NULL, status VARCHAR(20) NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, remote_id VARCHAR(255) DEFAULT NULL, last_synced_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, provider VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A4948FE77E3C61F9 ON external_account (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A4948FE77E3C61F992C4739C ON external_account (owner_id, provider)');
        $this->addSql('COMMENT ON COLUMN external_account.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN external_account.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN external_account.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN external_account.last_synced_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE oauth_attempt (id UUID NOT NULL, owner_id UUID NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, encrypted_code TEXT DEFAULT NULL, login_hint VARCHAR(253) DEFAULT NULL, authorization_url TEXT DEFAULT NULL, provider VARCHAR(20) NOT NULL, nonce_hash VARCHAR(64) NOT NULL, encrypted_verifier TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BEAAD137E3C61F9 ON oauth_attempt (owner_id)');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN oauth_attempt.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE oauth_sdk_store (id UUID NOT NULL, owner_id UUID NOT NULL, kind VARCHAR(20) NOT NULL, store_key VARCHAR(1000) NOT NULL, encrypted_value TEXT NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_22BB68647E3C61F9 ON oauth_sdk_store (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_22BB68647E3C61F93BC4BCD98B447160 ON oauth_sdk_store (owner_id, kind, store_key)');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN oauth_sdk_store.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE external_account ADD CONSTRAINT FK_A4948FE77E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth_attempt ADD CONSTRAINT FK_BEAAD137E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE oauth_sdk_store ADD CONSTRAINT FK_22BB68647E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_account DROP CONSTRAINT FK_A4948FE77E3C61F9');
        $this->addSql('ALTER TABLE oauth_attempt DROP CONSTRAINT FK_BEAAD137E3C61F9');
        $this->addSql('ALTER TABLE oauth_sdk_store DROP CONSTRAINT FK_22BB68647E3C61F9');
        $this->addSql('DROP TABLE external_account');
        $this->addSql('DROP TABLE oauth_attempt');
        $this->addSql('DROP TABLE oauth_sdk_store');
    }
}
