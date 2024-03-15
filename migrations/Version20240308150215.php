<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240308150215 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE vacation_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE vacation (id VARCHAR(255) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, reviewed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_confirmed BOOLEAN NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, note VARCHAR(255) NOT NULL, rejection_note VARCHAR(255) NOT NULL, requested_by_id VARCHAR DEFAULT NULL, reviewed_by_id VARCHAR DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E3DADF754DA1E751 ON vacation (requested_by_id)');
        $this->addSql('CREATE INDEX IDX_E3DADF75FC6B21F1 ON vacation (reviewed_by_id)');
        $this->addSql('ALTER TABLE vacation ADD CONSTRAINT FK_E3DADF754DA1E751 FOREIGN KEY (requested_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE vacation ADD CONSTRAINT FK_E3DADF75FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE vacation_id_seq CASCADE');
        $this->addSql('ALTER TABLE vacation DROP CONSTRAINT FK_E3DADF754DA1E751');
        $this->addSql('ALTER TABLE vacation DROP CONSTRAINT FK_E3DADF75FC6B21F1');
        $this->addSql('DROP TABLE vacation');
    }
}
