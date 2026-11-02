<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade contactos, ingresos, cobros, justificantes y el cierre de trimestre.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, display_name VARCHAR(180) NOT NULL, tax_id VARCHAR(20) NOT NULL, is_client TINYINT(1) NOT NULL, is_supplier TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_contact_entity (fiscal_entity_id), UNIQUE INDEX UNIQ_contact_entity_tax (fiscal_entity_id, tax_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, contact_id VARCHAR(36) NOT NULL, invoice_no VARCHAR(40) NOT NULL, issued_on DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', due_on DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', snapshot_name VARCHAR(180) NOT NULL, snapshot_tax_id VARCHAR(20) NOT NULL, currency VARCHAR(3) NOT NULL, record_status VARCHAR(32) NOT NULL, version INT NOT NULL, annulled_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', annul_reason VARCHAR(500) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_income_entity (fiscal_entity_id), INDEX IDX_income_contact (contact_id), INDEX IDX_income_issued (fiscal_entity_id, issued_on), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income_line (id VARCHAR(36) NOT NULL, income_id VARCHAR(36) NOT NULL, description VARCHAR(180) NOT NULL, quantity NUMERIC(12, 4) NOT NULL, unit_price NUMERIC(12, 4) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, withholding_rate NUMERIC(5, 2) NOT NULL, base_amount NUMERIC(12, 2) NOT NULL, tax_amount NUMERIC(12, 2) NOT NULL, withholding_amount NUMERIC(12, 2) NOT NULL, line_total NUMERIC(12, 2) NOT NULL, position INT NOT NULL, INDEX IDX_income_line_income (income_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income_payment (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, income_id VARCHAR(36) NOT NULL, amount NUMERIC(12, 2) NOT NULL, paid_on DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', method VARCHAR(32) NOT NULL, reference VARCHAR(80) NOT NULL, client_token VARCHAR(36) NOT NULL, INDEX IDX_income_payment_entity (fiscal_entity_id), INDEX IDX_income_payment_income (income_id), UNIQUE INDEX UNIQ_income_payment_token (income_id, client_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income_action (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, income_id VARCHAR(36) NOT NULL, account_id VARCHAR(36) NOT NULL, action VARCHAR(32) NOT NULL, summary VARCHAR(255) NOT NULL, acted_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_income_action_entity (fiscal_entity_id), INDEX IDX_income_action_income (income_id), INDEX IDX_income_action_account (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE income_attachment (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, income_id VARCHAR(36) NOT NULL, file_name VARCHAR(180) NOT NULL, mime_type VARCHAR(80) NOT NULL, object_key VARCHAR(255) NOT NULL, bytes INT NOT NULL, sha256 VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_income_attachment_entity (fiscal_entity_id), INDEX IDX_income_attachment_income (income_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE closed_quarter (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, closed_by_id VARCHAR(36) NOT NULL, year INT NOT NULL, quarter INT NOT NULL, closed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_closed_quarter_entity (fiscal_entity_id), INDEX IDX_closed_quarter_account (closed_by_id), UNIQUE INDEX UNIQ_closed_quarter_entity (fiscal_entity_id, year, quarter), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_contact_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_income_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income ADD CONSTRAINT FK_income_contact FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE income_line ADD CONSTRAINT FK_income_line_income FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_payment ADD CONSTRAINT FK_income_payment_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_payment ADD CONSTRAINT FK_income_payment_income FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_action ADD CONSTRAINT FK_income_action_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_action ADD CONSTRAINT FK_income_action_income FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_action ADD CONSTRAINT FK_income_action_account FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_attachment ADD CONSTRAINT FK_income_attachment_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE income_attachment ADD CONSTRAINT FK_income_attachment_income FOREIGN KEY (income_id) REFERENCES income (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE closed_quarter ADD CONSTRAINT FK_closed_quarter_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE closed_quarter ADD CONSTRAINT FK_closed_quarter_account FOREIGN KEY (closed_by_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE closed_quarter DROP FOREIGN KEY FK_closed_quarter_entity');
        $this->addSql('ALTER TABLE closed_quarter DROP FOREIGN KEY FK_closed_quarter_account');
        $this->addSql('ALTER TABLE income_attachment DROP FOREIGN KEY FK_income_attachment_entity');
        $this->addSql('ALTER TABLE income_attachment DROP FOREIGN KEY FK_income_attachment_income');
        $this->addSql('ALTER TABLE income_action DROP FOREIGN KEY FK_income_action_entity');
        $this->addSql('ALTER TABLE income_action DROP FOREIGN KEY FK_income_action_income');
        $this->addSql('ALTER TABLE income_action DROP FOREIGN KEY FK_income_action_account');
        $this->addSql('ALTER TABLE income_payment DROP FOREIGN KEY FK_income_payment_entity');
        $this->addSql('ALTER TABLE income_payment DROP FOREIGN KEY FK_income_payment_income');
        $this->addSql('ALTER TABLE income_line DROP FOREIGN KEY FK_income_line_income');
        $this->addSql('ALTER TABLE income DROP FOREIGN KEY FK_income_entity');
        $this->addSql('ALTER TABLE income DROP FOREIGN KEY FK_income_contact');
        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_contact_entity');
        $this->addSql('DROP TABLE closed_quarter');
        $this->addSql('DROP TABLE income_attachment');
        $this->addSql('DROP TABLE income_action');
        $this->addSql('DROP TABLE income_payment');
        $this->addSql('DROP TABLE income_line');
        $this->addSql('DROP TABLE income');
        $this->addSql('DROP TABLE contact');
    }
}
