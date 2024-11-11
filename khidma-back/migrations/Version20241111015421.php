<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241111015421 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE review (id SERIAL NOT NULL, service_id INT NOT NULL, author_id INT NOT NULL, rating SMALLINT NOT NULL, comment TEXT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_verified BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_794381C6ED5CA9E6 ON review (service_id)');
        $this->addSql('CREATE INDEX IDX_794381C6F675F31B ON review (author_id)');
        $this->addSql('COMMENT ON COLUMN review.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN review.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6F675F31B FOREIGN KEY (author_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE service ADD average_rating NUMERIC(3, 2) DEFAULT NULL');
        $this->addSql('ALTER TABLE service ADD reviews_count INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6ED5CA9E6');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6F675F31B');
        $this->addSql('DROP TABLE review');
        $this->addSql('ALTER TABLE service DROP average_rating');
        $this->addSql('ALTER TABLE service DROP reviews_count');
    }
}
