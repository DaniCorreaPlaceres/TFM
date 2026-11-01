<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261101193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade el secreto TOTP cifrado y los códigos de recuperación de un solo uso.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account ADD totp_secret VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE TABLE recovery_code (id VARCHAR(36) NOT NULL, account_id VARCHAR(36) NOT NULL, code_hash VARCHAR(64) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_recovery_code_account (account_id), UNIQUE INDEX UNIQ_recovery_code_account_hash (account_id, code_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE recovery_code ADD CONSTRAINT FK_recovery_code_account FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recovery_code DROP FOREIGN KEY FK_recovery_code_account');
        $this->addSql('DROP TABLE recovery_code');
        $this->addSql('ALTER TABLE account DROP totp_secret');
    }
}
