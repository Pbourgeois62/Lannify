<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250908164813 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE need (id SERIAL NOT NULL, event_id INT NOT NULL, created_by_id INT DEFAULT NULL, label VARCHAR(255) NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E6F46C4471F7E88B ON need (event_id)');
        $this->addSql('CREATE INDEX IDX_E6F46C44B03A8386 ON need (created_by_id)');
        $this->addSql('CREATE TABLE need_contribution (id SERIAL NOT NULL, need_id INT NOT NULL, user_id INT NOT NULL, quantity INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3E4CFD0624AF264 ON need_contribution (need_id)');
        $this->addSql('CREATE INDEX IDX_E3E4CFD0A76ED395 ON need_contribution (user_id)');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C4471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE need ADD CONSTRAINT FK_E6F46C44B03A8386 FOREIGN KEY (created_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE need_contribution ADD CONSTRAINT FK_E3E4CFD0624AF264 FOREIGN KEY (need_id) REFERENCES need (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE need_contribution ADD CONSTRAINT FK_E3E4CFD0A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT fk_8d93d64986383b10');
        $this->addSql('DROP INDEX uniq_8d93d64986383b10');
        $this->addSql('ALTER TABLE "user" DROP avatar_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE need DROP CONSTRAINT FK_E6F46C4471F7E88B');
        $this->addSql('ALTER TABLE need DROP CONSTRAINT FK_E6F46C44B03A8386');
        $this->addSql('ALTER TABLE need_contribution DROP CONSTRAINT FK_E3E4CFD0624AF264');
        $this->addSql('ALTER TABLE need_contribution DROP CONSTRAINT FK_E3E4CFD0A76ED395');
        $this->addSql('DROP TABLE need');
        $this->addSql('DROP TABLE need_contribution');
        $this->addSql('ALTER TABLE "user" ADD avatar_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT fk_8d93d64986383b10 FOREIGN KEY (avatar_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_8d93d64986383b10 ON "user" (avatar_id)');
    }
}
