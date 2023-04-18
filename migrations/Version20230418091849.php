<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230418091849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE hiring_plan_user (hiring_plan_id INT NOT NULL, user_id INT NOT NULL, PRIMARY KEY(hiring_plan_id, user_id))');
        $this->addSql('CREATE INDEX IDX_9E1D4332A6E6B99 ON hiring_plan_user (hiring_plan_id)');
        $this->addSql('CREATE INDEX IDX_9E1D4332A76ED395 ON hiring_plan_user (user_id)');
        $this->addSql('ALTER TABLE hiring_plan_user ADD CONSTRAINT FK_9E1D4332A6E6B99 FOREIGN KEY (hiring_plan_id) REFERENCES hiring_plan (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE hiring_plan_user ADD CONSTRAINT FK_9E1D4332A76ED395 FOREIGN KEY (user_id) REFERENCES "manager_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE hiring_plan_user DROP CONSTRAINT FK_9E1D4332A6E6B99');
        $this->addSql('ALTER TABLE hiring_plan_user DROP CONSTRAINT FK_9E1D4332A76ED395');
        $this->addSql('DROP TABLE hiring_plan_user');
    }
}
