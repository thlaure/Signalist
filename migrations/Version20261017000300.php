<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017000300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a dispatch lease to weekly digests to bound queue fan-out and retries.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekly_digest ADD dispatched_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql("COMMENT ON COLUMN weekly_digest.dispatched_at IS '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE weekly_digest DROP dispatched_at');
    }
}
