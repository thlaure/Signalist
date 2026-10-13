<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012000700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Shared workspaces, membership, curation and per-member reading state';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_read_state (id UUID NOT NULL, article_id UUID NOT NULL, user_id UUID NOT NULL, is_read BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0CA7FAE7294869C ON article_read_state (article_id)');
        $this->addSql('CREATE INDEX IDX_C0CA7FAEA76ED395 ON article_read_state (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0CA7FAE7294869CA76ED395 ON article_read_state (article_id, user_id)');
        $this->addSql('COMMENT ON COLUMN article_read_state.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_read_state.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_read_state.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE bookmark_annotation (id UUID NOT NULL, bookmark_id UUID NOT NULL, author_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, body TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_87BDC6492741D25 ON bookmark_annotation (bookmark_id)');
        $this->addSql('CREATE INDEX IDX_87BDC64F675F31B ON bookmark_annotation (author_id)');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.bookmark_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.author_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN bookmark_annotation.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE organization (id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN organization.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE organization_activity (id UUID NOT NULL, organization_id UUID NOT NULL, actor_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, action VARCHAR(40) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_436CCBF832C8A3DE ON organization_activity (organization_id)');
        $this->addSql('CREATE INDEX IDX_436CCBF810DAF24A ON organization_activity (actor_id)');
        $this->addSql('COMMENT ON COLUMN organization_activity.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_activity.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_activity.actor_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_activity.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE organization_invitation (id UUID NOT NULL, organization_id UUID NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, email VARCHAR(180) NOT NULL, role VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1846F34D32C8A3DE ON organization_invitation (organization_id)');
        $this->addSql('COMMENT ON COLUMN organization_invitation.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_invitation.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_invitation.expires_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN organization_invitation.accepted_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE organization_member (id UUID NOT NULL, organization_id UUID NOT NULL, user_id UUID NOT NULL, role VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_756A2A8D32C8A3DE ON organization_member (organization_id)');
        $this->addSql('CREATE INDEX IDX_756A2A8DA76ED395 ON organization_member (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_756A2A8D32C8A3DEA76ED395 ON organization_member (organization_id, user_id)');
        $this->addSql('COMMENT ON COLUMN organization_member.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_member.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN organization_member.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE shared_bookmark (id UUID NOT NULL, organization_id UUID NOT NULL, article_id UUID NOT NULL, curator_id UUID DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_8F46633E32C8A3DE ON shared_bookmark (organization_id)');
        $this->addSql('CREATE INDEX IDX_8F46633E7294869C ON shared_bookmark (article_id)');
        $this->addSql('CREATE INDEX IDX_8F46633E733D5B5D ON shared_bookmark (curator_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8F46633E32C8A3DE7294869C ON shared_bookmark (organization_id, article_id)');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.article_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.curator_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN shared_bookmark.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE article_read_state ADD CONSTRAINT FK_C0CA7FAE7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article_read_state ADD CONSTRAINT FK_C0CA7FAEA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookmark_annotation ADD CONSTRAINT FK_87BDC6492741D25 FOREIGN KEY (bookmark_id) REFERENCES shared_bookmark (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bookmark_annotation ADD CONSTRAINT FK_87BDC64F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_activity ADD CONSTRAINT FK_436CCBF832C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_activity ADD CONSTRAINT FK_436CCBF810DAF24A FOREIGN KEY (actor_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_invitation ADD CONSTRAINT FK_1846F34D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8D32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization_member ADD CONSTRAINT FK_756A2A8DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shared_bookmark ADD CONSTRAINT FK_8F46633E32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shared_bookmark ADD CONSTRAINT FK_8F46633E7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE shared_bookmark ADD CONSTRAINT FK_8F46633E733D5B5D FOREIGN KEY (curator_id) REFERENCES "user" (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE article ALTER enriched_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN article.enriched_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE article_search ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE article_search ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE article_search ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN article_search.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_search.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN article_search.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX uniq_category_slug_owner');
        $this->addSql('ALTER TABLE category ADD organization_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN category.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE category ADD CONSTRAINT FK_64C19C132C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_64C19C132C8A3DE ON category (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug_organization ON category (slug, organization_id) WHERE organization_id IS NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug_owner ON category (slug, owner_id) WHERE organization_id IS NULL');
        $this->addSql('ALTER TABLE data_access_audit ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE data_access_audit ALTER user_id TYPE UUID');
        $this->addSql('ALTER TABLE data_access_audit ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN data_access_audit.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN data_access_audit.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN data_access_audit.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('DROP INDEX uniq_feed_url_owner');
        $this->addSql('ALTER TABLE feed ADD organization_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN feed.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE feed ADD CONSTRAINT FK_234044AB32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_234044AB32C8A3DE ON feed (organization_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url_organization ON feed (url, organization_id) WHERE organization_id IS NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url_owner ON feed (url, owner_id) WHERE organization_id IS NULL');
        $this->addSql('ALTER TABLE newsletter ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter ALTER next_run_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE newsletter ALTER generated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE newsletter ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN newsletter.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN newsletter.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN newsletter.next_run_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN newsletter.generated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN newsletter.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE newsletter_run ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter_run ALTER newsletter_id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter_run ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN newsletter_run.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN newsletter_run.newsletter_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN newsletter_run.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX newsletter_run_newsletter_id_period_key RENAME TO UNIQ_B853928D22DB1917C5B81ECE');
        $this->addSql('ALTER TABLE user_consent ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE user_consent ALTER user_id TYPE UUID');
        $this->addSql('ALTER TABLE user_consent ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN user_consent.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_consent.user_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN user_consent.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX idx_consent_user_purpose_date RENAME TO IDX_3B1F161AA76ED395B887B3EB8B8E8428');
        $this->addSql('ALTER TABLE waitlist_entry ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE waitlist_entry ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE waitlist_entry ALTER confirmed_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.confirmed_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER INDEX waitlist_entry_email_key RENAME TO UNIQ_67447574E7927C74');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf((int) $this->connection->fetchOne('SELECT COUNT(*) FROM category WHERE organization_id IS NOT NULL') > 0, 'Remove or export shared workspace data before rolling back this migration.');
        $this->addSql('ALTER TABLE category DROP CONSTRAINT FK_64C19C132C8A3DE');
        $this->addSql('ALTER TABLE feed DROP CONSTRAINT FK_234044AB32C8A3DE');
        $this->addSql('ALTER TABLE article_read_state DROP CONSTRAINT FK_C0CA7FAE7294869C');
        $this->addSql('ALTER TABLE article_read_state DROP CONSTRAINT FK_C0CA7FAEA76ED395');
        $this->addSql('ALTER TABLE bookmark_annotation DROP CONSTRAINT FK_87BDC6492741D25');
        $this->addSql('ALTER TABLE bookmark_annotation DROP CONSTRAINT FK_87BDC64F675F31B');
        $this->addSql('ALTER TABLE organization_activity DROP CONSTRAINT FK_436CCBF832C8A3DE');
        $this->addSql('ALTER TABLE organization_activity DROP CONSTRAINT FK_436CCBF810DAF24A');
        $this->addSql('ALTER TABLE organization_invitation DROP CONSTRAINT FK_1846F34D32C8A3DE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8D32C8A3DE');
        $this->addSql('ALTER TABLE organization_member DROP CONSTRAINT FK_756A2A8DA76ED395');
        $this->addSql('ALTER TABLE shared_bookmark DROP CONSTRAINT FK_8F46633E32C8A3DE');
        $this->addSql('ALTER TABLE shared_bookmark DROP CONSTRAINT FK_8F46633E7294869C');
        $this->addSql('ALTER TABLE shared_bookmark DROP CONSTRAINT FK_8F46633E733D5B5D');
        $this->addSql('DROP TABLE article_read_state');
        $this->addSql('DROP TABLE bookmark_annotation');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE organization_activity');
        $this->addSql('DROP TABLE organization_invitation');
        $this->addSql('DROP TABLE organization_member');
        $this->addSql('DROP TABLE shared_bookmark');
        $this->addSql('ALTER TABLE article ALTER enriched_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN article.enriched_at IS NULL');
        $this->addSql('ALTER TABLE data_access_audit ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE data_access_audit ALTER user_id TYPE UUID');
        $this->addSql('ALTER TABLE data_access_audit ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN data_access_audit.id IS NULL');
        $this->addSql('COMMENT ON COLUMN data_access_audit.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN data_access_audit.created_at IS NULL');
        $this->addSql('ALTER TABLE user_consent ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE user_consent ALTER user_id TYPE UUID');
        $this->addSql('ALTER TABLE user_consent ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN user_consent.id IS NULL');
        $this->addSql('COMMENT ON COLUMN user_consent.user_id IS NULL');
        $this->addSql('COMMENT ON COLUMN user_consent.created_at IS NULL');
        $this->addSql('ALTER INDEX idx_3b1f161aa76ed395b887b3eb8b8e8428 RENAME TO idx_consent_user_purpose_date');
        $this->addSql('DROP INDEX IDX_64C19C132C8A3DE');
        $this->addSql('DROP INDEX uniq_category_slug_organization');
        $this->addSql('DROP INDEX uniq_category_slug_owner');
        $this->addSql('ALTER TABLE category DROP organization_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_category_slug_owner ON category (slug, owner_id)');
        $this->addSql('DROP INDEX IDX_234044AB32C8A3DE');
        $this->addSql('DROP INDEX uniq_feed_url_organization');
        $this->addSql('DROP INDEX uniq_feed_url_owner');
        $this->addSql('ALTER TABLE feed DROP organization_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_feed_url_owner ON feed (url, owner_id)');
        $this->addSql('ALTER TABLE waitlist_entry ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE waitlist_entry ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE waitlist_entry ALTER confirmed_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.id IS NULL');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN waitlist_entry.confirmed_at IS NULL');
        $this->addSql('ALTER INDEX uniq_67447574e7927c74 RENAME TO waitlist_entry_email_key');
        $this->addSql('ALTER TABLE newsletter ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE newsletter ALTER next_run_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE newsletter ALTER generated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN newsletter.id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.created_at IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.next_run_at IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter.generated_at IS NULL');
        $this->addSql('ALTER TABLE newsletter_run ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter_run ALTER newsletter_id TYPE UUID');
        $this->addSql('ALTER TABLE newsletter_run ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN newsletter_run.id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter_run.newsletter_id IS NULL');
        $this->addSql('COMMENT ON COLUMN newsletter_run.created_at IS NULL');
        $this->addSql('ALTER INDEX uniq_b853928d22db1917c5b81ece RENAME TO newsletter_run_newsletter_id_period_key');
        $this->addSql('ALTER TABLE article_search ALTER id TYPE UUID');
        $this->addSql('ALTER TABLE article_search ALTER owner_id TYPE UUID');
        $this->addSql('ALTER TABLE article_search ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN article_search.id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_search.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN article_search.created_at IS NULL');
    }
}
