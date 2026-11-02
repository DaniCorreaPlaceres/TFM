<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102191000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade las categorías de un nivel y el registro manual del gasto.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE expense_category (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, name VARCHAR(60) NOT NULL, is_default TINYINT(1) NOT NULL, active TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_expense_category_entity (fiscal_entity_id), UNIQUE INDEX uniq_expense_category_entity_name (fiscal_entity_id, name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expense (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, contact_id VARCHAR(36) NOT NULL, category_id VARCHAR(36) NOT NULL, kind VARCHAR(16) NOT NULL, payment_method VARCHAR(32) NOT NULL, document_no VARCHAR(40) NOT NULL, issued_on DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', snapshot_name VARCHAR(180) NOT NULL, snapshot_tax_id VARCHAR(20) NOT NULL, snapshot_category VARCHAR(60) NOT NULL, currency VARCHAR(3) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_expense_entity (fiscal_entity_id), INDEX IDX_expense_contact (contact_id), INDEX IDX_expense_category (category_id), INDEX IDX_expense_issued (fiscal_entity_id, issued_on), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE expense_line (id VARCHAR(36) NOT NULL, expense_id VARCHAR(36) NOT NULL, description VARCHAR(180) NOT NULL, quantity NUMERIC(12, 4) NOT NULL, unit_price NUMERIC(12, 4) NOT NULL, tax_rate NUMERIC(5, 2) NOT NULL, withholding_rate NUMERIC(5, 2) NOT NULL, base_amount NUMERIC(12, 2) NOT NULL, tax_amount NUMERIC(12, 2) NOT NULL, withholding_amount NUMERIC(12, 2) NOT NULL, line_total NUMERIC(12, 2) NOT NULL, position INT NOT NULL, INDEX IDX_expense_line_expense (expense_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expense_category ADD CONSTRAINT FK_expense_category_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_expense_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_expense_contact FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_expense_category FOREIGN KEY (category_id) REFERENCES expense_category (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE expense_line ADD CONSTRAINT FK_expense_line_expense FOREIGN KEY (expense_id) REFERENCES expense (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense_line DROP FOREIGN KEY FK_expense_line_expense');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_expense_entity');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_expense_contact');
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_expense_category');
        $this->addSql('ALTER TABLE expense_category DROP FOREIGN KEY FK_expense_category_entity');
        $this->addSql('DROP TABLE expense_line');
        $this->addSql('DROP TABLE expense');
        $this->addSql('DROP TABLE expense_category');
    }
}
