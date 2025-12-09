<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209031925 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add date column to activite table and fix invalid dates';
    }

    public function up(Schema $schema): void
    {
        $connection = $this->connection;
        
        // Check if column exists
        $columnExists = $connection->executeQuery(
            "SELECT COUNT(*) as cnt FROM information_schema.columns 
             WHERE table_schema = DATABASE() 
             AND table_name = 'activite' 
             AND column_name = 'date'"
        )->fetchOne();
        
        // Add column only if it doesn't exist (as nullable first)
        if ((int)$columnExists === 0) {
            $this->addSql('ALTER TABLE activite ADD COLUMN date DATETIME NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        }
        
        // Fix any invalid dates - handle the '0000-00-00 00:00:00' issue
        // Use a safer approach by checking the column value as string
        try {
            $this->addSql("UPDATE activite 
                SET date = CURRENT_TIMESTAMP 
                WHERE date IS NULL 
                OR CAST(date AS CHAR(20)) = '0000-00-00 00:00:00'
                OR date < '1970-01-01 00:00:00'");
        } catch (\Exception $e) {
            // If update fails due to invalid data, set all to current timestamp
            $this->addSql('UPDATE activite SET date = CURRENT_TIMESTAMP');
        }
        
        // Now make column NOT NULL
        $this->addSql("ALTER TABLE activite MODIFY COLUMN date DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP date');
    }
}
