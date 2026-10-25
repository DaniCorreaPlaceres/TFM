<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261025190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añade la versión de sesión y el enlace de recuperación de contraseña.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE account ADD session_version INT DEFAULT 0 NOT NULL');
        $this->addSql('CREATE TABLE password_reset (id VARCHAR(36) NOT NULL, account_id VARCHAR(36) NOT NULL, token_hash VARCHAR(64) NOT NULL, expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', used_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_password_reset_token (token_hash), INDEX IDX_password_reset_account (account_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE password_reset ADD CONSTRAINT FK_password_reset_account FOREIGN KEY (account_id) REFERENCES account (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE password_reset DROP FOREIGN KEY FK_password_reset_account');
        $this->addSql('DROP TABLE password_reset');
        $this->addSql('ALTER TABLE account DROP session_version');
    }
}
