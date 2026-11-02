<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'income_action')]
class IncomeAction
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Income $income,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $account,
        #[ORM\Column(length: 32)]
        private string $action,
        #[ORM\Column(length: 255)]
        private string $summary,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $actedAt,
    ) {
    }

    public static function record(
        Income $income,
        Account $account,
        string $action,
        string $summary,
        \DateTimeImmutable $at,
    ): self {
        return new self(Uuid::v7()->toRfc4122(), $income->fiscalEntity(), $income, $account, $action, $summary, $at);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function income(): Income
    {
        return $this->income;
    }

    public function account(): Account
    {
        return $this->account;
    }

    public function action(): string
    {
        return $this->action;
    }

    public function summary(): string
    {
        return $this->summary;
    }

    public function actedAt(): \DateTimeImmutable
    {
        return $this->actedAt;
    }
}
