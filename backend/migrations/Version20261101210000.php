<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261101210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade la entidad fiscal y la pertenencia del titular.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE fiscal_entity (id VARCHAR(36) NOT NULL, legal_name VARCHAR(180) NOT NULL, tax_id VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fiscal_entity_membership (id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, account_id VARCHAR(36) NOT NULL, role VARCHAR(32) NOT NULL, INDEX IDX_fiscal_membership_entity (fiscal_entity_id), INDEX IDX_fiscal_membership_account (account_id), UNIQUE INDEX UNIQ_fiscal_membership_entity_account (fiscal_entity_id, account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fiscal_entity_membership ADD CONSTRAINT FK_fiscal_membership_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE fiscal_entity_membership ADD CONSTRAINT FK_fiscal_membership_account FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE fiscal_entity_membership DROP FOREIGN KEY FK_fiscal_membership_entity');
        $this->addSql('ALTER TABLE fiscal_entity_membership DROP FOREIGN KEY FK_fiscal_membership_account');
        $this->addSql('DROP TABLE fiscal_entity_membership');
        $this->addSql('DROP TABLE fiscal_entity');
    }
}
