<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102015000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade el permiso del empleado para ver las entidades vinculadas.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE advisory_employee_permission (id VARCHAR(36) NOT NULL, membership_id VARCHAR(36) NOT NULL, permission_code VARCHAR(32) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_advisory_employee_permission_membership (membership_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory_employee_permission ADD CONSTRAINT FK_advisory_employee_permission_membership FOREIGN KEY (membership_id) REFERENCES advisory_membership (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory_employee_permission DROP FOREIGN KEY FK_advisory_employee_permission_membership');
        $this->addSql('DROP TABLE advisory_employee_permission');
    }
}
