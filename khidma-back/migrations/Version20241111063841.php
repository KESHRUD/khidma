<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241111063841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add location relation to service and handle existing data';
    }

    public function up(Schema $schema): void
    {
        // 2. Ajouter la nouvelle colonne location_id
        $this->addSql('ALTER TABLE service ADD location_id INT DEFAULT NULL');

        // 3. Associer les services existants à la location par défaut
        $this->addSql('UPDATE service SET location_id = (SELECT id FROM location ORDER BY id ASC LIMIT 1) WHERE location_id IS NULL');

        // 4. Supprimer l'ancienne colonne location si elle existe
        $this->addSql('ALTER TABLE service DROP COLUMN IF EXISTS location');

        // 5. Ajouter la contrainte de clé étrangère
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD264D218E FOREIGN KEY (location_id) REFERENCES location (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        
        // 6. Créer l'index
        $this->addSql('CREATE INDEX IDX_E19D9AD264D218E ON service (location_id)');
    }

    public function down(Schema $schema): void
    {
        // 1. Supprimer la contrainte de clé étrangère et l'index
        $this->addSql('ALTER TABLE service DROP CONSTRAINT FK_E19D9AD264D218E');
        $this->addSql('DROP INDEX IDX_E19D9AD264D218E');

        // 2. Ajouter temporairement une colonne location
        $this->addSql('ALTER TABLE service ADD location VARCHAR(255) DEFAULT NULL');

        // 3. Copier les données si nécessaire
        $this->addSql('UPDATE service SET location = \'Migrated Location\'');

        // 4. Supprimer la colonne location_id
        $this->addSql('ALTER TABLE service DROP COLUMN location_id');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}