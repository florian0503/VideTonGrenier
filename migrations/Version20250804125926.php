<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250804125926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE report (id INT AUTO_INCREMENT NOT NULL, reporter_id INT NOT NULL, reported_user_id INT NOT NULL, annonce_id INT DEFAULT NULL, reviewed_by_id INT DEFAULT NULL, type VARCHAR(50) NOT NULL, reason LONGTEXT NOT NULL, description LONGTEXT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, reviewed_at DATETIME DEFAULT NULL, admin_comment LONGTEXT DEFAULT NULL, INDEX IDX_C42F7784E1CFE6F5 (reporter_id), INDEX IDX_C42F7784E7566E (reported_user_id), INDEX IDX_C42F77848805AB2F (annonce_id), INDEX IDX_C42F7784FC6B21F1 (reviewed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E1CFE6F5 FOREIGN KEY (reporter_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784E7566E FOREIGN KEY (reported_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F77848805AB2F FOREIGN KEY (annonce_id) REFERENCES annonce (id)');
        $this->addSql('ALTER TABLE report ADD CONSTRAINT FK_C42F7784FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user ADD banned_by_id INT DEFAULT NULL, ADD is_banned TINYINT(1) NOT NULL, ADD banned_at DATETIME DEFAULT NULL, ADD ban_reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649386B8E7 FOREIGN KEY (banned_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649386B8E7 ON user (banned_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E1CFE6F5');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784E7566E');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F77848805AB2F');
        $this->addSql('ALTER TABLE report DROP FOREIGN KEY FK_C42F7784FC6B21F1');
        $this->addSql('DROP TABLE report');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649386B8E7');
        $this->addSql('DROP INDEX IDX_8D93D649386B8E7 ON user');
        $this->addSql('ALTER TABLE user DROP banned_by_id, DROP is_banned, DROP banned_at, DROP ban_reason');
    }
}
