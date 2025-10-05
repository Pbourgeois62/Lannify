<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251005110400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE feedback_messages_id_seq CASCADE');
        $this->addSql('CREATE TABLE feedback_message (id SERIAL NOT NULL, sender_id INT NOT NULL, feedback_id INT NOT NULL, content VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_27E410DCF624B39D ON feedback_message (sender_id)');
        $this->addSql('CREATE INDEX IDX_27E410DCD249A887 ON feedback_message (feedback_id)');
        $this->addSql('COMMENT ON COLUMN feedback_message.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE feedback_message ADD CONSTRAINT FK_27E410DCF624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedback_message ADD CONSTRAINT FK_27E410DCD249A887 FOREIGN KEY (feedback_id) REFERENCES feedback (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedback_messages_user DROP CONSTRAINT fk_4be3425f2e72ea9e');
        $this->addSql('ALTER TABLE feedback_messages_user DROP CONSTRAINT fk_4be3425fa76ed395');
        $this->addSql('DROP TABLE feedback_messages_user');
        $this->addSql('DROP TABLE feedback_messages');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE feedback_messages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE feedback_messages_user (feedback_messages_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(feedback_messages_id, user_id))');
        $this->addSql('CREATE INDEX idx_4be3425f2e72ea9e ON feedback_messages_user (feedback_messages_id)');
        $this->addSql('CREATE INDEX idx_4be3425fa76ed395 ON feedback_messages_user (user_id)');
        $this->addSql('CREATE TABLE feedback_messages (id SERIAL NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE feedback_messages_user ADD CONSTRAINT fk_4be3425f2e72ea9e FOREIGN KEY (feedback_messages_id) REFERENCES feedback_messages (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedback_messages_user ADD CONSTRAINT fk_4be3425fa76ed395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE feedback_message DROP CONSTRAINT FK_27E410DCF624B39D');
        $this->addSql('ALTER TABLE feedback_message DROP CONSTRAINT FK_27E410DCD249A887');
        $this->addSql('DROP TABLE feedback_message');
    }
}
