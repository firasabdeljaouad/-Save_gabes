<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209033315 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Fix invalid datetime values - MySQL doesn't accept '0000-00-00 00:00:00'
        // Update all invalid dates to current timestamp
        $this->addSql("UPDATE activite SET date = CURRENT_TIMESTAMP 
            WHERE date IS NULL 
            OR date = '0000-00-00 00:00:00' 
            OR date < '1970-01-01 00:00:00'");
        
        // Ensure the column exists and is properly configured
        // If column already exists with invalid structure, modify it
        $this->addSql("ALTER TABLE activite 
            MODIFY COLUMN date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        // Revert changes if needed
        $this->addSql('ALTER TABLE activite DROP COLUMN date');
    }
}
