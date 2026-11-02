<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'expense_category')]
#[ORM\UniqueConstraint(name: 'uniq_expense_category_entity_name', columns: ['fiscal_entity_id', 'name'])]
class ExpenseCategory
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\Column(length: 60)]
        private string $name,
        #[ORM\Column]
        private bool $isDefault,
        #[ORM\Column]
        private bool $active,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt,
    ) {
    }

    public static function open(FiscalEntity $fiscalEntity, string $name, bool $isDefault, \DateTimeImmutable $createdAt): self
    {
        return new self(Uuid::v7()->toRfc4122(), $fiscalEntity, $name, $isDefault, true, $createdAt);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function belongsTo(FiscalEntity $fiscalEntity): bool
    {
        return $this->fiscalEntity->id() === $fiscalEntity->id();
    }

    public function name(): string
    {
        return $this->name;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
