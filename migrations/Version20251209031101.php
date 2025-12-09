<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209031101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Skip if column already exists - this migration is redundant if Version20251209031925 ran first
        // Just ensure data is valid
        $this->addSql("UPDATE activite SET date = CURRENT_TIMESTAMP 
            WHERE date IS NULL 
            OR date = '0000-00-00 00:00:00' 
            OR CAST(date AS CHAR) < '1970-01-01 00:00:00'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activite DROP date');
    }
}
