<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102230000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade los pagos y el estado de revisión del gasto.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE expense ADD review_state VARCHAR(16) DEFAULT 'borrador' NOT NULL, ADD review_reason VARCHAR(240) DEFAULT NULL");
        $this->addSql('CREATE TABLE expense_payment (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, expense_id VARCHAR(36) NOT NULL, amount NUMERIC(12, 2) NOT NULL, paid_on DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', method VARCHAR(32) NOT NULL, reference VARCHAR(80) NOT NULL, client_token VARCHAR(36) NOT NULL, INDEX IDX_expense_payment_entity (fiscal_entity_id), INDEX IDX_expense_payment_expense (expense_id), UNIQUE INDEX UNIQ_expense_payment_token (expense_id, client_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE expense_payment ADD CONSTRAINT FK_expense_payment_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE expense_payment ADD CONSTRAINT FK_expense_payment_expense FOREIGN KEY (expense_id) REFERENCES expense (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense_payment DROP FOREIGN KEY FK_expense_payment_entity');
        $this->addSql('ALTER TABLE expense_payment DROP FOREIGN KEY FK_expense_payment_expense');
        $this->addSql('DROP TABLE expense_payment');
        $this->addSql('ALTER TABLE expense DROP review_state, DROP review_reason');
    }
}
