<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'advisory_membership')]
#[ORM\UniqueConstraint(name: 'UNIQ_advisory_membership_firm_account', columns: ['advisory_firm_id', 'account_id'])]
class AdvisoryMembership
{
    public const string ADMIN = 'administrador';

    public const string EMPLOYEE = 'empleado';

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private AdvisoryFirm $advisoryFirm,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $account,
        #[ORM\Column(length: 32)]
        private string $role,
    ) {
    }

    public static function administer(AdvisoryFirm $advisoryFirm, Account $account): self
    {
        return new self(Uuid::v7()->toRfc4122(), $advisoryFirm, $account, self::ADMIN);
    }

    public static function employ(AdvisoryFirm $advisoryFirm, Account $account): self
    {
        return new self(Uuid::v7()->toRfc4122(), $advisoryFirm, $account, self::EMPLOYEE);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function advisoryFirm(): AdvisoryFirm
    {
        return $this->advisoryFirm;
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function belongsTo(Account $account): bool
    {
        return $this->account->id() === $account->id();
    }

    public function role(): string
    {
        return $this->role;
    }

    public function isAdmin(): bool
    {
        return self::ADMIN === $this->role;
    }
}
