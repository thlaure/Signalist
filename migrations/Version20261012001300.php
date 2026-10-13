<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261012001300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Owner-scoped tag catalog';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag (id UUID NOT NULL, owner_id UUID NOT NULL, name VARCHAR(60) NOT NULL, name_key VARCHAR(60) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_389B7837E3C61F9 ON tag (owner_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_389B7837E3C61F98E85A347 ON tag (owner_id, name_key)');
        $this->addSql('COMMENT ON COLUMN tag.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN tag.owner_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B7837E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag DROP CONSTRAINT FK_389B7837E3C61F9');
        $this->addSql('DROP TABLE tag');
    }
}
