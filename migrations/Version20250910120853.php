<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250910120853 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE participant_game (id SERIAL NOT NULL, participant_id INT DEFAULT NULL, game_id INT DEFAULT NULL, owns BOOLEAN NOT NULL, interested BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F79F23B29D1C3019 ON participant_game (participant_id)');
        $this->addSql('CREATE INDEX IDX_F79F23B2E48FD905 ON participant_game (game_id)');
        $this->addSql('ALTER TABLE participant_game ADD CONSTRAINT FK_F79F23B29D1C3019 FOREIGN KEY (participant_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE participant_game ADD CONSTRAINT FK_F79F23B2E48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE participant_game DROP CONSTRAINT FK_F79F23B29D1C3019');
        $this->addSql('ALTER TABLE participant_game DROP CONSTRAINT FK_F79F23B2E48FD905');
        $this->addSql('DROP TABLE participant_game');
    }
}
