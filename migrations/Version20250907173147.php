<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250907173147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ADD profile_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FCCFA12B8 FOREIGN KEY (profile_id) REFERENCES profile (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C53D045FCCFA12B8 ON image (profile_id)');
        $this->addSql('ALTER TABLE profile DROP avatar_name');
        $this->addSql('ALTER TABLE profile DROP updated_at');
        $this->addSql('ALTER TABLE profile RENAME COLUMN avatar_size TO avatar_id');
        $this->addSql('ALTER TABLE profile ADD CONSTRAINT FK_8157AA0F86383B10 FOREIGN KEY (avatar_id) REFERENCES image (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8157AA0F86383B10 ON profile (avatar_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045FCCFA12B8');
        $this->addSql('DROP INDEX UNIQ_C53D045FCCFA12B8');
        $this->addSql('ALTER TABLE image DROP profile_id');
        $this->addSql('ALTER TABLE profile DROP CONSTRAINT FK_8157AA0F86383B10');
        $this->addSql('DROP INDEX UNIQ_8157AA0F86383B10');
        $this->addSql('ALTER TABLE profile ADD avatar_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE profile ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE profile RENAME COLUMN avatar_id TO avatar_size');
        $this->addSql('COMMENT ON COLUMN profile.updated_at IS \'(DC2Type:datetime_immutable)\'');
    }
}
