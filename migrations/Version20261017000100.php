<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261017000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add immutable personal weekly digest history and processing outbox.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE weekly_digest (id UUID NOT NULL, owner_id UUID NOT NULL, period VARCHAR(10) NOT NULL, status VARCHAR(20) NOT NULL, attempts INT NOT NULL, items JSON NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DIGEST_OWNER ON weekly_digest (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_digest_owner_period ON weekly_digest (owner_id, period)');
        $this->addSql('ALTER TABLE weekly_digest ADD CONSTRAINT FK_DIGEST_OWNER FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE weekly_digest');
    }
}
