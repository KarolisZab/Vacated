<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240418074732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reserved_day_tag (reserved_day_id VARCHAR NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(reserved_day_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_382E76BB4698F50 ON reserved_day_tag (reserved_day_id)');
        $this->addSql('CREATE INDEX IDX_382E76BBAD26311 ON reserved_day_tag (tag_id)');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, name VARCHAR(255) NOT NULL, color_code VARCHAR(7) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE user_tag (user_id VARCHAR NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(user_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_E89FD608A76ED395 ON user_tag (user_id)');
        $this->addSql('CREATE INDEX IDX_E89FD608BAD26311 ON user_tag (tag_id)');
        $this->addSql('ALTER TABLE reserved_day_tag ADD CONSTRAINT FK_382E76BB4698F50 FOREIGN KEY (reserved_day_id) REFERENCES reserved_day (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reserved_day_tag ADD CONSTRAINT FK_382E76BBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tag ADD CONSTRAINT FK_E89FD608A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_tag ADD CONSTRAINT FK_E89FD608BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('ALTER TABLE reserved_day_tag DROP CONSTRAINT FK_382E76BB4698F50');
        $this->addSql('ALTER TABLE reserved_day_tag DROP CONSTRAINT FK_382E76BBAD26311');
        $this->addSql('ALTER TABLE user_tag DROP CONSTRAINT FK_E89FD608A76ED395');
        $this->addSql('ALTER TABLE user_tag DROP CONSTRAINT FK_E89FD608BAD26311');
        $this->addSql('DROP TABLE reserved_day_tag');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE user_tag');
    }
}
