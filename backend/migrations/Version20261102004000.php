<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261102004000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade el vínculo entre una entidad fiscal y una asesoría, y su invitación.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE advisory_client_link (id VARCHAR(36) NOT NULL, advisory_firm_id VARCHAR(36) NOT NULL, fiscal_entity_id VARCHAR(36) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_advisory_client_link_entity (fiscal_entity_id), INDEX IDX_advisory_client_link_firm (advisory_firm_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE advisory_client_invitation (id VARCHAR(36) NOT NULL, origin VARCHAR(16) NOT NULL, fiscal_entity_id VARCHAR(36) DEFAULT NULL, advisory_firm_id VARCHAR(36) DEFAULT NULL, issuer_id VARCHAR(36) NOT NULL, email VARCHAR(180) NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_advisory_client_invitation_token (token_hash), INDEX IDX_advisory_client_invitation_entity (fiscal_entity_id), INDEX IDX_advisory_client_invitation_firm (advisory_firm_id), INDEX IDX_advisory_client_invitation_issuer (issuer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory_client_link ADD CONSTRAINT FK_advisory_client_link_firm FOREIGN KEY (advisory_firm_id) REFERENCES advisory_firm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_client_link ADD CONSTRAINT FK_advisory_client_link_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_client_invitation ADD CONSTRAINT FK_advisory_client_invitation_entity FOREIGN KEY (fiscal_entity_id) REFERENCES fiscal_entity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_client_invitation ADD CONSTRAINT FK_advisory_client_invitation_firm FOREIGN KEY (advisory_firm_id) REFERENCES advisory_firm (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE advisory_client_invitation ADD CONSTRAINT FK_advisory_client_invitation_issuer FOREIGN KEY (issuer_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory_client_link DROP FOREIGN KEY FK_advisory_client_link_firm');
        $this->addSql('ALTER TABLE advisory_client_link DROP FOREIGN KEY FK_advisory_client_link_entity');
        $this->addSql('ALTER TABLE advisory_client_invitation DROP FOREIGN KEY FK_advisory_client_invitation_entity');
        $this->addSql('ALTER TABLE advisory_client_invitation DROP FOREIGN KEY FK_advisory_client_invitation_firm');
        $this->addSql('ALTER TABLE advisory_client_invitation DROP FOREIGN KEY FK_advisory_client_invitation_issuer');
        $this->addSql('DROP TABLE advisory_client_invitation');
        $this->addSql('DROP TABLE advisory_client_link');
    }
}
