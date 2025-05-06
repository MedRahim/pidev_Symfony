<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250504231414 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post ADD CONSTRAINT FK_BA5AE01DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_BA5AE01DA76ED395 ON blog_post (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5F9E962AA76ED395 ON comments (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE SET NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E52FFDEEA76ED395 ON orders (user_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_like ADD user_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_like ADD CONSTRAINT FK_653627B8A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_653627B8A76ED395 ON post_like (user_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP FOREIGN KEY FK_BA5AE01DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_BA5AE01DA76ED395 ON blog_post
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE blog_post DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5F9E962AA76ED395 ON comments
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP user_id
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E52FFDEEA76ED395 ON orders
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_like DROP FOREIGN KEY FK_653627B8A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_653627B8A76ED395 ON post_like
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post_like DROP user_id
        SQL);
    }
}
