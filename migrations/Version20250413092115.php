<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250413092115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation CHANGE client_id client_id INT NOT NULL, CHANGE datee datee VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL, CHANGE state state TINYINT(1) NOT NULL, CHANGE type type VARCHAR(255) NOT NULL, CHANGE photo photo VARCHAR(255) NOT NULL, CHANGE Priorite priorite VARCHAR(255) NOT NULL, CHANGE email email VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse DROP FOREIGN KEY reponse_ibfk_1
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX reclamationId ON reponse
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse CHANGE message message VARCHAR(255) NOT NULL, CHANGE datee datee VARCHAR(255) NOT NULL, CHANGE reclamationId reclamation_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5FB6DEC72D6BA2D9 ON reponse (reclamation_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reclamation CHANGE client_id client_id INT DEFAULT NULL, CHANGE datee datee VARCHAR(255) DEFAULT NULL, CHANGE description description TEXT DEFAULT NULL, CHANGE state state TINYINT(1) DEFAULT NULL, CHANGE type type VARCHAR(100) DEFAULT NULL, CHANGE photo photo VARCHAR(255) DEFAULT NULL, CHANGE priorite Priorite VARCHAR(100) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5FB6DEC72D6BA2D9 ON reponse
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse CHANGE message message TEXT DEFAULT NULL, CHANGE datee datee VARCHAR(255) DEFAULT NULL, CHANGE reclamation_id reclamationId INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reponse ADD CONSTRAINT reponse_ibfk_1 FOREIGN KEY (reclamationId) REFERENCES reclamation (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX reclamationId ON reponse (reclamationId)
        SQL);
    }
}
