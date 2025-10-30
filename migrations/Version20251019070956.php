<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019070956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commentary DROP CONSTRAINT fk_1cac12caf624b39d');
        $this->addSql('DROP INDEX idx_1cac12caf624b39d');
        $this->addSql('ALTER TABLE commentary RENAME COLUMN sender_id TO author_id');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CAF675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_1CAC12CAF675F31B ON commentary (author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commentary DROP CONSTRAINT FK_1CAC12CAF675F31B');
        $this->addSql('DROP INDEX IDX_1CAC12CAF675F31B');
        $this->addSql('ALTER TABLE commentary RENAME COLUMN author_id TO sender_id');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT fk_1cac12caf624b39d FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_1cac12caf624b39d ON commentary (sender_id)');
    }
}
