<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102220000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade la deducibilidad independiente de la base y de la cuota del gasto.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense ADD base_deductible TINYINT(1) DEFAULT NULL, ADD base_reason VARCHAR(240) DEFAULT NULL, ADD tax_deductible TINYINT(1) DEFAULT NULL, ADD tax_reason VARCHAR(240) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE expense DROP base_deductible, DROP base_reason, DROP tax_deductible, DROP tax_reason');
    }
}
