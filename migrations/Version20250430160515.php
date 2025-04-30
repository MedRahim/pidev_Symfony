<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250430160515 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // 1. Allow NULL temporarily to fix existing data
        $this->addSql('ALTER TABLE user MODIFY roles LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');

        // 2. Update existing NULL values to ["ROLE_USER"]
        $this->addSql('UPDATE user SET roles = \'["ROLE_USER"]\' WHERE roles IS NULL');

        // 3. Enforce NOT NULL with a default value
        $this->addSql('ALTER TABLE user MODIFY roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\' DEFAULT \'["ROLE_USER"]\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user CHANGE roles roles JSON DEFAULT NULL COMMENT '(DC2Type:json)'
        SQL);
    }
}
