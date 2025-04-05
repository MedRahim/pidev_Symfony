<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250402095020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP INDEX fk_medecin_service ON medecin');
        $this->addSql('ALTER TABLE medecin CHANGE id_medecin id_medecin INT NOT NULL');
        $this->addSql('ALTER TABLE rendezvous MODIFY idRendezVous INT NOT NULL');
        $this->addSql('DROP INDEX fk_rendezvous_medecin ON rendezvous');
        $this->addSql('DROP INDEX `primary` ON rendezvous');
        $this->addSql('ALTER TABLE rendezvous ADD id_rendez_vous INT NOT NULL, ADD date_rendez_vous DATE NOT NULL, ADD time_rendez_vous VARCHAR(255) NOT NULL, ADD id_medecin INT NOT NULL, DROP idRendezVous, DROP dateRendezVous, DROP timeRendezVous, DROP idMedecin, CHANGE status status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE rendezvous ADD PRIMARY KEY (id_rendez_vous)');
        $this->addSql('ALTER TABLE servicehospitalier MODIFY idService INT NOT NULL');
        $this->addSql('DROP INDEX `primary` ON servicehospitalier');
        $this->addSql('ALTER TABLE servicehospitalier ADD id_service INT NOT NULL, ADD nombre_lits_disponibles INT NOT NULL, DROP idService, DROP nombreLitsDisponibles, CHANGE description description LONGTEXT NOT NULL, CHANGE nomService nom_service VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE servicehospitalier ADD PRIMARY KEY (id_service)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE medecin CHANGE id_medecin id_medecin INT AUTO_INCREMENT NOT NULL');
        $this->addSql('CREATE INDEX fk_medecin_service ON medecin (id_service)');
        $this->addSql('DROP INDEX `PRIMARY` ON rendezvous');
        $this->addSql('ALTER TABLE rendezvous ADD idRendezVous INT AUTO_INCREMENT NOT NULL, ADD dateRendezVous DATE DEFAULT NULL, ADD timeRendezVous TIME DEFAULT \'00:00:00\', ADD idMedecin INT DEFAULT NULL, DROP id_rendez_vous, DROP date_rendez_vous, DROP time_rendez_vous, DROP id_medecin, CHANGE status status VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX fk_rendezvous_medecin ON rendezvous (idMedecin)');
        $this->addSql('ALTER TABLE rendezvous ADD PRIMARY KEY (idRendezVous)');
        $this->addSql('DROP INDEX `PRIMARY` ON servicehospitalier');
        $this->addSql('ALTER TABLE servicehospitalier ADD idService INT AUTO_INCREMENT NOT NULL, ADD nombreLitsDisponibles INT DEFAULT 0, DROP id_service, DROP nombre_lits_disponibles, CHANGE description description TEXT DEFAULT NULL, CHANGE nom_service nomService VARCHAR(100) NOT NULL');
        $this->addSql('ALTER TABLE servicehospitalier ADD PRIMARY KEY (idService)');
    }
}
