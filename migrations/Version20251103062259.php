<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251103062259 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE game_session (id SERIAL NOT NULL, organizer_id INT NOT NULL, game_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, end_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4586AAFB876C4DDA ON game_session (organizer_id)');
        $this->addSql('CREATE INDEX IDX_4586AAFBE48FD905 ON game_session (game_id)');
        $this->addSql('COMMENT ON COLUMN game_session.start_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.end_date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN game_session.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE game_session_user (game_session_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(game_session_id, user_id))');
        $this->addSql('CREATE INDEX IDX_A532E20D8FE32B32 ON game_session_user (game_session_id)');
        $this->addSql('CREATE INDEX IDX_A532E20DA76ED395 ON game_session_user (user_id)');
        $this->addSql('ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFB876C4DDA FOREIGN KEY (organizer_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session ADD CONSTRAINT FK_4586AAFBE48FD905 FOREIGN KEY (game_id) REFERENCES game (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session_user ADD CONSTRAINT FK_A532E20D8FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_session (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE game_session_user ADD CONSTRAINT FK_A532E20DA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message ADD game_session_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F8FE32B32 FOREIGN KEY (game_session_id) REFERENCES game_session (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B6BD307F8FE32B32 ON message (game_session_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F8FE32B32');
        $this->addSql('ALTER TABLE game_session DROP CONSTRAINT FK_4586AAFB876C4DDA');
        $this->addSql('ALTER TABLE game_session DROP CONSTRAINT FK_4586AAFBE48FD905');
        $this->addSql('ALTER TABLE game_session_user DROP CONSTRAINT FK_A532E20D8FE32B32');
        $this->addSql('ALTER TABLE game_session_user DROP CONSTRAINT FK_A532E20DA76ED395');
        $this->addSql('DROP TABLE game_session');
        $this->addSql('DROP TABLE game_session_user');
        $this->addSql('DROP INDEX IDX_B6BD307F8FE32B32');
        $this->addSql('ALTER TABLE message DROP game_session_id');
    }
}
