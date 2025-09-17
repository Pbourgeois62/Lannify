<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250916041342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT fk_8157aa0f86383b10');
        $this->addSql('DROP INDEX uniq_8157aa0f86383b10');
        $this->addSql('ALTER TABLE profile DROP avatar_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE profile ADD avatar_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT fk_8157aa0f86383b10 FOREIGN KEY (avatar_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8157aa0f86383b10 ON profile (avatar_id)');
    }
}
