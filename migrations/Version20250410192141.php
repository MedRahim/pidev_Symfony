<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250410192141 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_like ADD blog_post_id INT NOT NULL');
        $this->addSql('ALTER TABLE post_like ADD CONSTRAINT FK_653627B8A77FBEAF FOREIGN KEY (blog_post_id) REFERENCES blog_post (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_653627B8A77FBEAF ON post_like (blog_post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8A77FBEAF');
        $this->addSql('DROP INDEX IDX_653627B8A77FBEAF ON post_like');
        $this->addSql('ALTER TABLE post_like DROP blog_post_id');
    }
}
