<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501162445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin ADD image_name VARCHAR(255) DEFAULT NULL, ADD image_size INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin RENAME INDEX fk_medecin_service TO IDX_1BDA53C6F124F120
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous CHANGE timeRendezVous timeRendezVous TIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous RENAME INDEX fk_medecin TO IDX_C09A9BA8B633834
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous RENAME INDEX fk_rendezvous_user TO IDX_C09A9BA86B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_8BA011DA57DCDB9A ON servicehospitalier (nomService)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX email TO UNIQ_1483A5E9E7927C74
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C6F124F120
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin DROP image_name, DROP image_size
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE medecin RENAME INDEX idx_1bda53c6f124f120 TO fk_medecin_service
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA8B633834
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous DROP FOREIGN KEY FK_C09A9BA86B3CA4B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous CHANGE timeRendezVous timeRendezVous TIME DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous RENAME INDEX idx_c09a9ba8b633834 TO fk_medecin
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rendezvous RENAME INDEX idx_c09a9ba86b3ca4b TO fk_rendezvous_user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_8BA011DA57DCDB9A ON servicehospitalier
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO email
        SQL);
    }
}
