<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250429103035 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category and created_at columns to product table';
    }

    public function up(Schema $schema): void
    {
        // Handle created_at column if it doesn't exist
        $this->addSql('ALTER TABLE product ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT NULL');
        $this->addSql('UPDATE product SET created_at = NOW() WHERE created_at IS NULL');
        $this->addSql('ALTER TABLE product MODIFY created_at DATETIME NOT NULL');

        // Handle category column
        $this->addSql('ALTER TABLE product ADD COLUMN IF NOT EXISTS category VARCHAR(100) DEFAULT NULL');
        $this->addSql('UPDATE product SET category = \'Uncategorized\' WHERE category IS NULL');
        $this->addSql('ALTER TABLE product MODIFY category VARCHAR(100) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product DROP COLUMN IF EXISTS category');
        $this->addSql('ALTER TABLE product DROP COLUMN IF EXISTS created_at');
    }
}
