<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230418090714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE hiring_plan_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE hiring_plan (id INT NOT NULL, status VARCHAR(255) NOT NULL, position VARCHAR(512) NOT NULL, expected_count INT NOT NULL, urgency VARCHAR(512) NOT NULL, director VARCHAR(512) NOT NULL, offers_count INT NOT NULL, employees_count INT NOT NULL, comment TEXT DEFAULT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE hiring_plan_id_seq CASCADE');
        $this->addSql('DROP TABLE hiring_plan');
    }
}
