<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'fiscal_entity_membership')]
#[ORM\UniqueConstraint(name: 'UNIQ_fiscal_membership_entity_account', columns: ['fiscal_entity_id', 'account_id'])]
class FiscalEntityMembership
{
    public const string HOLDER = 'titular';

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $account,
        #[ORM\Column(length: 32)]
        private string $role,
    ) {
    }

    public static function hold(FiscalEntity $fiscalEntity, Account $account): self
    {
        return new self(Uuid::v7()->toRfc4122(), $fiscalEntity, $account, self::HOLDER);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function belongsTo(Account $account): bool
    {
        return $this->account->id() === $account->id();
    }

    public function role(): string
    {
        return $this->role;
    }
}
