<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251208235546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add description of what this migration does';
    }

    public function up(Schema $schema): void
    {
        // Add your SQL changes here
        // Example:
        // $this->addSql('ALTER TABLE projet ADD COLUMN new_field VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        // Add rollback SQL here
        // Example:
        // $this->addSql('ALTER TABLE projet DROP COLUMN new_field');
    }
}
