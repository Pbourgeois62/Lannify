<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251104064041 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game_session ADD is_private BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE game_session ADD max_participants VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE game_session ADD cover_image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE game_session DROP is_private');
        $this->addSql('ALTER TABLE game_session DROP max_participants');
        $this->addSql('ALTER TABLE game_session DROP cover_image');
    }
}
