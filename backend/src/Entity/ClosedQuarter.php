<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'closed_quarter')]
#[ORM\UniqueConstraint(name: 'UNIQ_closed_quarter_entity', columns: ['fiscal_entity_id', 'year', 'quarter'])]
class ClosedQuarter
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\Column]
        private int $year,
        #[ORM\Column]
        private int $quarter,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Account $closedBy,
        #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
        private \DateTimeImmutable $closedAt,
    ) {
    }

    public static function close(
        FiscalEntity $fiscalEntity,
        int $year,
        int $quarter,
        Account $account,
        \DateTimeImmutable $now,
    ): self {
        return new self(Uuid::v7()->toRfc4122(), $fiscalEntity, $year, $quarter, $account, $now);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function quarter(): int
    {
        return $this->quarter;
    }

    public function closedBy(): Account
    {
        return $this->closedBy;
    }

    public function closedAt(): \DateTimeImmutable
    {
        return $this->closedAt;
    }
}
