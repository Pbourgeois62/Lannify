<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005104909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE feedback_messages (id SERIAL NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE feedback_messages_user (feedback_messages_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(feedback_messages_id, user_id))');
        $this->addSql('CREATE INDEX IDX_4BE3425F2E72EA9E ON feedback_messages_user (feedback_messages_id)');
        $this->addSql('CREATE INDEX IDX_4BE3425FA76ED395 ON feedback_messages_user (user_id)');
        $this->addSql('ALTER TABLE feedback_messages_user ADD CONSTRAINT FK_4BE3425F2E72EA9E FOREIGN KEY (feedback_messages_id) REFERENCES feedback_messages (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedback_messages_user ADD CONSTRAINT FK_4BE3425FA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT fk_b6bd307fd249a887');
        $this->addSql('DROP INDEX idx_b6bd307fd249a887');
        $this->addSql('ALTER TABLE message DROP feedback_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE feedback_messages_user DROP CONSTRAINT FK_4BE3425F2E72EA9E');
        $this->addSql('ALTER TABLE feedback_messages_user DROP CONSTRAINT FK_4BE3425FA76ED395');
        $this->addSql('DROP TABLE feedback_messages');
        $this->addSql('DROP TABLE feedback_messages_user');
        $this->addSql('ALTER TABLE message ADD feedback_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT fk_b6bd307fd249a887 FOREIGN KEY (feedback_id) REFERENCES feedback (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_b6bd307fd249a887 ON message (feedback_id)');
    }
}
