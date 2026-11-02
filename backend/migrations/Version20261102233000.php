<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102233000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade la recurrencia mensual y el enlace de la propuesta de gasto.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense ADD recurrence VARCHAR(16) DEFAULT NULL, ADD source_expense_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE expense ADD CONSTRAINT FK_expense_source FOREIGN KEY (source_expense_id) REFERENCES expense (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_expense_source ON expense (source_expense_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense DROP FOREIGN KEY FK_expense_source');
        $this->addSql('DROP INDEX IDX_expense_source ON expense');
        $this->addSql('ALTER TABLE expense DROP recurrence, DROP source_expense_id');
    }
}
