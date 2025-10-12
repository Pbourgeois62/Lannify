<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251012115406 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event ADD magic_token VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE event DROP magic_link');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3BAE0AA7E7D7E81C ON event (magic_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_3BAE0AA7E7D7E81C');
        $this->addSql('ALTER TABLE event ADD magic_link VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE event DROP magic_token');
    }
}
