<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250417225018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE badges (id VARCHAR(50) NOT NULL, description VARCHAR(255) DEFAULT 'NULL', image_path VARCHAR(255) DEFAULT 'NULL', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cities (name VARCHAR(100) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY(name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE mariem (id INT AUTO_INCREMENT NOT NULL, m1 VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT '(DC2Type:json)', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_C43F965C054072F (m1), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payments (payment_id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, reservation_id INT DEFAULT NULL, amount NUMERIC(10, 2) NOT NULL, method VARCHAR(50) NOT NULL, payment_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX IDX_65D29B32A76ED395 (user_id), INDEX IDX_65D29B32B83297E7 (reservation_id), PRIMARY KEY(payment_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservations (id INT AUTO_INCREMENT NOT NULL, trip_id INT DEFAULT NULL, user_id INT DEFAULT NULL, reservation_time DATETIME DEFAULT NULL, status VARCHAR(20) DEFAULT 'pending', transport_id INT NOT NULL, seat_number INT NOT NULL, payment_status VARCHAR(20) DEFAULT 'pending', seat_type VARCHAR(20) DEFAULT 'Standard', INDEX IDX_4DA239A5BC2E0E (trip_id), INDEX IDX_4DA239A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transport_types (transport_id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, description LONGTEXT DEFAULT NULL, capacity INT NOT NULL, PRIMARY KEY(transport_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE trips (id INT AUTO_INCREMENT NOT NULL, transport_id INT DEFAULT NULL, departure VARCHAR(100) NOT NULL, destination VARCHAR(100) NOT NULL, departure_time DATETIME NOT NULL, arrival_time DATETIME NOT NULL, price NUMERIC(10, 2) NOT NULL, transport_name VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, distance DOUBLE PRECISION NOT NULL, capacity INT DEFAULT 50 NOT NULL, image VARCHAR(255) DEFAULT NULL, INDEX IDX_AA7370DA9909C13F (transport_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE villes (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, histoire LONGTEXT DEFAULT NULL, anecdotes LONGTEXT DEFAULT NULL, activites LONGTEXT DEFAULT NULL, gastronomie LONGTEXT DEFAULT NULL, nature LONGTEXT DEFAULT NULL, histoire_interactive LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payments ADD CONSTRAINT FK_65D29B32A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payments ADD CONSTRAINT FK_65D29B32B83297E7 FOREIGN KEY (reservation_id) REFERENCES reservations (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A5BC2E0E FOREIGN KEY (trip_id) REFERENCES trips (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations ADD CONSTRAINT FK_4DA239A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trips ADD CONSTRAINT FK_AA7370DA9909C13F FOREIGN KEY (transport_id) REFERENCES transport_types (transport_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE payments DROP FOREIGN KEY FK_65D29B32B83297E7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A5BC2E0E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations DROP FOREIGN KEY FK_4DA239A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE trips DROP FOREIGN KEY FK_AA7370DA9909C13F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE badges
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cities
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE mariem
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE payments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservations
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transport_types
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE trips
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE villes
        SQL);
    }
}
