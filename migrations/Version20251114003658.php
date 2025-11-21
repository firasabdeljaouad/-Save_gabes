<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251114003658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE donater (id INT AUTO_INCREMENT NOT NULL, donations_id INT DEFAULT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(180) DEFAULT NULL, registration_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_4BD61C33E7927C74 (email), INDEX IDX_4BD61C331E85A36D (donations_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE donation (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, amount VARCHAR(180) NOT NULL, payment_method VARCHAR(50) NOT NULL, status VARCHAR(50) NOT NULL, transaction_id VARCHAR(255) DEFAULT NULL, is_anonymous TINYINT(1) NOT NULL, INDEX IDX_31E581A0166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, target_amount NUMERIC(10, 2) NOT NULL, start_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE donater ADD CONSTRAINT FK_4BD61C331E85A36D FOREIGN KEY (donations_id) REFERENCES donation (id)');
        $this->addSql('ALTER TABLE donation ADD CONSTRAINT FK_31E581A0166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE donater DROP FOREIGN KEY FK_4BD61C331E85A36D');
        $this->addSql('ALTER TABLE donation DROP FOREIGN KEY FK_31E581A0166D1F9C');
        $this->addSql('DROP TABLE donater');
        $this->addSql('DROP TABLE donation');
        $this->addSql('DROP TABLE project');
    }
}
