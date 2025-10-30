<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251019070127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commentary (id SERIAL NOT NULL, sender_id INT NOT NULL, event_image_id INT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, content VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_1CAC12CAF624B39D ON commentary (sender_id)');
        $this->addSql('CREATE INDEX IDX_1CAC12CA7B49CCBD ON commentary (event_image_id)');
        $this->addSql('COMMENT ON COLUMN commentary.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CAF624B39D FOREIGN KEY (sender_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commentary ADD CONSTRAINT FK_1CAC12CA7B49CCBD FOREIGN KEY (event_image_id) REFERENCES event_image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commentary DROP CONSTRAINT FK_1CAC12CAF624B39D');
        $this->addSql('ALTER TABLE commentary DROP CONSTRAINT FK_1CAC12CA7B49CCBD');
        $this->addSql('DROP TABLE commentary');
    }
}
