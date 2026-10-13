<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261013001400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store scoped article question answers';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_search ADD answer TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE article_search DROP answer');
    }
}
