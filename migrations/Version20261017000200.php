<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017000200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align weekly digest Doctrine type metadata and owner index.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("COMMENT ON COLUMN weekly_digest.id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN weekly_digest.owner_id IS '(DC2Type:uuid)'");
        $this->addSql("COMMENT ON COLUMN weekly_digest.sent_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql("COMMENT ON COLUMN weekly_digest.updated_at IS '(DC2Type:datetime_immutable)'");
        $this->addSql('ALTER INDEX idx_digest_owner RENAME TO IDX_108382FF7E3C61F9');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('COMMENT ON COLUMN weekly_digest.id IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.owner_id IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.sent_at IS NULL');
        $this->addSql('COMMENT ON COLUMN weekly_digest.updated_at IS NULL');
        $this->addSql('ALTER INDEX IDX_108382FF7E3C61F9 RENAME TO idx_digest_owner');
    }
}
