<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240315124352 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE reserved_day_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reserved_day (id VARCHAR(255) NOT NULL, date_from TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, date_to TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, note VARCHAR(255) NOT NULL, reserved_by_id VARCHAR DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4564CAE0BCDB4AF4 ON reserved_day (reserved_by_id)');
        $this->addSql('ALTER TABLE reserved_day ADD CONSTRAINT FK_4564CAE0BCDB4AF4 FOREIGN KEY (reserved_by_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE reserved_day_id_seq CASCADE');
        $this->addSql('ALTER TABLE reserved_day DROP CONSTRAINT FK_4564CAE0BCDB4AF4');
        $this->addSql('DROP TABLE reserved_day');
    }
}
