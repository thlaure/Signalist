<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012000800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Subscription scope, billing jobs, cached entitlements and usage budgets';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE billing_event (id VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, processed BOOLEAN NOT NULL, customer_id VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN billing_event.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE billing_link (id UUID NOT NULL, subscription_id UUID NOT NULL, requested_by_id UUID NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(20) NOT NULL, url VARCHAR(2048) DEFAULT NULL, kind VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_49DEA7369A1887DC ON billing_link (subscription_id)');
        $this->addSql('CREATE INDEX IDX_49DEA7364DA1E751 ON billing_link (requested_by_id)');
        $this->addSql('COMMENT ON COLUMN billing_link.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN billing_link.subscription_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN billing_link.requested_by_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN billing_link.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE subscription (id UUID NOT NULL, owner_id UUID DEFAULT NULL, organization_id UUID DEFAULT NULL, customer_id VARCHAR(255) DEFAULT NULL, status VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D39395C3F3 ON subscription (customer_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D37E3C61F9 ON subscription (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D332C8A3DE ON subscription (organization_id)');
        $this->addSql('COMMENT ON COLUMN subscription.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN subscription.organization_id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE usage_counter (id UUID NOT NULL, scope_key VARCHAR(60) NOT NULL, metric VARCHAR(30) NOT NULL, period VARCHAR(10) NOT NULL, used INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FA6E267B0F2E85987D62EE3C5B81ECE ON usage_counter (scope_key, metric, period)');
        $this->addSql('COMMENT ON COLUMN usage_counter.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE billing_link ADD CONSTRAINT FK_49DEA7369A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE billing_link ADD CONSTRAINT FK_49DEA7364DA1E751 FOREIGN KEY (requested_by_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT chk_subscription_scope CHECK (num_nonnulls(owner_id, organization_id) = 1)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D37E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D332C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE organization ADD plan VARCHAR(20) DEFAULT \'free\' NOT NULL');
        $this->addSql('ALTER TABLE organization ADD paid_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN organization.paid_until IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE "user" ADD plan VARCHAR(20) DEFAULT \'free\' NOT NULL');
        $this->addSql('ALTER TABLE "user" ADD paid_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN "user".paid_until IS \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billing_link DROP CONSTRAINT FK_49DEA7369A1887DC');
        $this->addSql('ALTER TABLE billing_link DROP CONSTRAINT FK_49DEA7364DA1E751');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D37E3C61F9');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D332C8A3DE');
        $this->addSql('DROP TABLE billing_event');
        $this->addSql('DROP TABLE billing_link');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE usage_counter');
        $this->addSql('ALTER TABLE "user" DROP plan');
        $this->addSql('ALTER TABLE "user" DROP paid_until');
        $this->addSql('ALTER TABLE organization DROP plan');
        $this->addSql('ALTER TABLE organization DROP paid_until');
    }
}
