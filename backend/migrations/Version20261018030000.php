<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ejemplo reversible de E0.3. No forma parte del esquema fiscal de DOC-06.
 */
final class Version20261018030000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Crea la tabla de ejemplo e0_example.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE e0_example (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(64) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE e0_example');
    }
}
