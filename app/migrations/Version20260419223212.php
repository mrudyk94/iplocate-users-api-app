<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260419223212 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE phone_numbers (phone VARCHAR(13) NOT NULL, id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, INDEX IDX_E7DC46CBA76ED395 (user_id), UNIQUE INDEX phone_unique_idx (phone), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (firstName VARCHAR(100) NOT NULL, lastName VARCHAR(100) NOT NULL, ip VARCHAR(45) DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE phone_numbers ADD CONSTRAINT FK_E7DC46CBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE phone_numbers DROP FOREIGN KEY FK_E7DC46CBA76ED395');
        $this->addSql('DROP TABLE phone_numbers');
        $this->addSql('DROP TABLE user');
    }
}
