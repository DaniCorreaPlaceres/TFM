<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261101223000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade la asesoría, sus miembros y las invitaciones de empleados.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE advisory_firm (id VARCHAR(36) NOT NULL, name VARCHAR(180) NOT NULL, tax_id VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advisory_membership (id VARCHAR(36) NOT NULL, advisory_firm_id VARCHAR(36) NOT NULL, account_id VARCHAR(36) NOT NULL, role VARCHAR(32) NOT NULL, INDEX IDX_advisory_membership_firm (advisory_firm_id), INDEX IDX_advisory_membership_account (account_id), UNIQUE INDEX UNIQ_advisory_membership_firm_account (advisory_firm_id, account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advisory_invitation (id VARCHAR(36) NOT NULL, advisory_firm_id VARCHAR(36) NOT NULL, issuer_id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_advisory_invitation_token (token_hash), INDEX IDX_advisory_invitation_firm (advisory_firm_id), INDEX IDX_advisory_invitation_issuer (issuer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory_membership ADD CONSTRAINT FK_advisory_membership_firm FOREIGN KEY (advisory_firm_id) REFERENCES advisory_firm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_membership ADD CONSTRAINT FK_advisory_membership_account FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_invitation ADD CONSTRAINT FK_advisory_invitation_firm FOREIGN KEY (advisory_firm_id) REFERENCES advisory_firm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_invitation ADD CONSTRAINT FK_advisory_invitation_issuer FOREIGN KEY (issuer_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory_invitation DROP FOREIGN KEY FK_advisory_invitation_firm');
        $this->addSql('ALTER TABLE advisory_invitation DROP FOREIGN KEY FK_advisory_invitation_issuer');
        $this->addSql('ALTER TABLE advisory_membership DROP FOREIGN KEY FK_advisory_membership_firm');
        $this->addSql('ALTER TABLE advisory_membership DROP FOREIGN KEY FK_advisory_membership_account');
        $this->addSql('DROP TABLE advisory_invitation');
        $this->addSql('DROP TABLE advisory_membership');
        $this->addSql('DROP TABLE advisory_firm');
    }
}
