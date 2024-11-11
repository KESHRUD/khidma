<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241111014247 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image ADD owner_id INT NOT NULL');
        $this->addSql('ALTER TABLE image ALTER service_id DROP NOT NULL');
        $this->addSql('ALTER TABLE image RENAME COLUMN file_name TO file_path');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_C53D045F7E3C61F9 ON image (owner_id)');
        $this->addSql('ALTER TABLE service ADD price NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE service ADD is_active BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE service ADD location VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD latitude NUMERIC(10, 8) DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD longitude NUMERIC(11, 8) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE image DROP CONSTRAINT FK_C53D045F7E3C61F9');
        $this->addSql('DROP INDEX IDX_C53D045F7E3C61F9');
        $this->addSql('ALTER TABLE image DROP owner_id');
        $this->addSql('ALTER TABLE image ALTER service_id SET NOT NULL');
        $this->addSql('ALTER TABLE image RENAME COLUMN file_path TO file_name');
        $this->addSql('ALTER TABLE service DROP price');
        $this->addSql('ALTER TABLE service DROP is_active');
        $this->addSql('ALTER TABLE service DROP location');
        $this->addSql('ALTER TABLE service DROP latitude');
        $this->addSql('ALTER TABLE service DROP longitude');
    }
}
