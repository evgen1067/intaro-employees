<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230414134340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE company_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE department_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE employee_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE "manager_user_id_seq" INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE company (id INT NOT NULL, name VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE department (id INT NOT NULL, name VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE employee (id INT NOT NULL, company_id INT NOT NULL, name VARCHAR(500) NOT NULL, gender SMALLINT NOT NULL, date_of_birth DATE DEFAULT NULL, date_of_employment DATE DEFAULT NULL, position VARCHAR(500) NOT NULL, status SMALLINT NOT NULL, date_of_dismissal DATE DEFAULT NULL, reason_of_dismissal SMALLINT DEFAULT NULL, category_of_dismissal SMALLINT DEFAULT NULL, competence VARCHAR(500) NOT NULL, grade VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_5D9F75A1979B1AD6 ON employee (company_id)');
        $this->addSql('CREATE TABLE employee_department (employee_id INT NOT NULL, department_id INT NOT NULL, PRIMARY KEY(employee_id, department_id))');
        $this->addSql('CREATE INDEX IDX_55CA515E8C03F15C ON employee_department (employee_id)');
        $this->addSql('CREATE INDEX IDX_55CA515EAE80F5DF ON employee_department (department_id)');
        $this->addSql('CREATE TABLE "manager_user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(500) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8B9A7430E7927C74 ON "manager_user" (email)');
        $this->addSql('CREATE TABLE user_department (user_id INT NOT NULL, department_id INT NOT NULL, PRIMARY KEY(user_id, department_id))');
        $this->addSql('CREATE INDEX IDX_6A7A2766A76ED395 ON user_department (user_id)');
        $this->addSql('CREATE INDEX IDX_6A7A2766AE80F5DF ON user_department (department_id)');
        $this->addSql('CREATE TABLE user_company (user_id INT NOT NULL, company_id INT NOT NULL, PRIMARY KEY(user_id, company_id))');
        $this->addSql('CREATE INDEX IDX_17B21745A76ED395 ON user_company (user_id)');
        $this->addSql('CREATE INDEX IDX_17B21745979B1AD6 ON user_company (company_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE employee ADD CONSTRAINT FK_5D9F75A1979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_department ADD CONSTRAINT FK_55CA515E8C03F15C FOREIGN KEY (employee_id) REFERENCES employee (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE employee_department ADD CONSTRAINT FK_55CA515EAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_department ADD CONSTRAINT FK_6A7A2766A76ED395 FOREIGN KEY (user_id) REFERENCES "manager_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_department ADD CONSTRAINT FK_6A7A2766AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_company ADD CONSTRAINT FK_17B21745A76ED395 FOREIGN KEY (user_id) REFERENCES "manager_user" (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_company ADD CONSTRAINT FK_17B21745979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE company_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE department_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE employee_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE "manager_user_id_seq" CASCADE');
        $this->addSql('ALTER TABLE employee DROP CONSTRAINT FK_5D9F75A1979B1AD6');
        $this->addSql('ALTER TABLE employee_department DROP CONSTRAINT FK_55CA515E8C03F15C');
        $this->addSql('ALTER TABLE employee_department DROP CONSTRAINT FK_55CA515EAE80F5DF');
        $this->addSql('ALTER TABLE user_department DROP CONSTRAINT FK_6A7A2766A76ED395');
        $this->addSql('ALTER TABLE user_department DROP CONSTRAINT FK_6A7A2766AE80F5DF');
        $this->addSql('ALTER TABLE user_company DROP CONSTRAINT FK_17B21745A76ED395');
        $this->addSql('ALTER TABLE user_company DROP CONSTRAINT FK_17B21745979B1AD6');
        $this->addSql('DROP TABLE company');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE employee');
        $this->addSql('DROP TABLE employee_department');
        $this->addSql('DROP TABLE "manager_user"');
        $this->addSql('DROP TABLE user_department');
        $this->addSql('DROP TABLE user_company');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
