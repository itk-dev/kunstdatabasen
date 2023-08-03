<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230801120357 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // The default migrations metadata storage table name has changed from
        // `migration_versions` to `doctrine_migration_versions` (cf.
        // https://github.com/doctrine/DoctrineMigrationsBundle/blob/3.2.x/UPGRADE.md#from-2x-to-300).
        // Therefore the migration_versions table only exists if we're upgrading
        // an existing database, i.e. not making a fresh install of the
        // database schema.
        $this->addSql('DROP TABLE IF EXISTS migration_versions');
        $this->addSql('ALTER TABLE image ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, executed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE image DROP created_at');
    }
}
