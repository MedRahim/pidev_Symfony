<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250427230016 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Only add created_at if it does not already exist
        // $this->addSql('ALTER TABLE product ADD created_at DATETIME DEFAULT NULL');
        // $this->addSql('UPDATE product SET created_at = NOW() WHERE created_at IS NULL');
        // $this->addSql('ALTER TABLE product ADD created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product DROP created_at');
    }
}
