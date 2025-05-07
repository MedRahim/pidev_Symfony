<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507151239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, cin VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON DEFAULT NULL, phone VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, pathtopic VARCHAR(255) NOT NULL, birthday DATE NOT NULL, is_verified TINYINT(1) NOT NULL, account_creation_date DATETIME NOT NULL, last_login_date DATETIME NOT NULL, failed_login_attempts INT NOT NULL, bio LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, google_id VARCHAR(255) DEFAULT NULL, avatar VARCHAR(255) DEFAULT NULL, google_authenticator_secret VARCHAR(255) DEFAULT NULL, is_google_authenticator_enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA86B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C6F124F120
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA8B633834
        SQL);
    }
}
