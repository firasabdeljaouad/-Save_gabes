<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251113235655 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dons DROP FOREIGN KEY FK_E4F955FA3DD7B7A7');
        $this->addSql('ALTER TABLE dons DROP FOREIGN KEY FK_E4F955FAF639F774');
        $this->addSql('DROP TABLE campaign');
        $this->addSql('DROP TABLE donateur');
        $this->addSql('DROP TABLE dons');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE campaign (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(150) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, start_date DATETIME DEFAULT NULL, end_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE donateur (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, email VARCHAR(150) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(20) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE dons (id INT AUTO_INCREMENT NOT NULL, donor_id INT DEFAULT NULL, campaign_id INT DEFAULT NULL, amount DOUBLE PRECISION NOT NULL, message VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_E4F955FA3DD7B7A7 (donor_id), INDEX IDX_E4F955FAF639F774 (campaign_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE dons ADD CONSTRAINT FK_E4F955FA3DD7B7A7 FOREIGN KEY (donor_id) REFERENCES donateur (id)');
        $this->addSql('ALTER TABLE dons ADD CONSTRAINT FK_E4F955FAF639F774 FOREIGN KEY (campaign_id) REFERENCES campaign (id)');
    }
}
