<?php

declare(strict_types=1);

namespace App\Entity;

use App\Income\DraftLine;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'expense_line')]
class ExpenseLine
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne(inversedBy: 'lines')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Expense $expense,
        #[ORM\Column(length: 180)]
        private string $description,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4)]
        private string $quantity,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 4)]
        private string $unitPrice,
        #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
        private string $taxRate,
        #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
        private string $withholdingRate,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
        private string $baseAmount,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
        private string $taxAmount,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
        private string $withholdingAmount,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
        private string $lineTotal,
        #[ORM\Column]
        private int $position,
    ) {
    }

    public static function fromDraft(Expense $expense, DraftLine $line, int $position): self
    {
        $values = $line->values;

        return new self(
            Uuid::v7()->toRfc4122(),
            $expense,
            $line->description,
            $values->quantity,
            $values->unitPrice,
            $values->taxRate,
            $values->withholdingRate,
            $values->base,
            $values->tax,
            $values->withholding,
            $values->total,
            $position,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function expense(): Expense
    {
        return $this->expense;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): string
    {
        return $this->quantity;
    }

    public function unitPrice(): string
    {
        return $this->unitPrice;
    }

    public function taxRate(): string
    {
        return $this->taxRate;
    }

    public function withholdingRate(): string
    {
        return $this->withholdingRate;
    }

    public function baseAmount(): string
    {
        return $this->baseAmount;
    }

    public function taxAmount(): string
    {
        return $this->taxAmount;
    }

    public function withholdingAmount(): string
    {
        return $this->withholdingAmount;
    }

    public function total(): string
    {
        return $this->lineTotal;
    }

    public function position(): int
    {
        return $this->position;
    }
}
