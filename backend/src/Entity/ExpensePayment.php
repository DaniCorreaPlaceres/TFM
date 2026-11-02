<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'expense_payment')]
#[ORM\UniqueConstraint(name: 'UNIQ_expense_payment_token', columns: ['expense_id', 'client_token'])]
class ExpensePayment
{
    public function __construct(
        #[ORM\Id]
        #[ORM\Column(length: 36)]
        private string $id,
        #[ORM\ManyToOne]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private FiscalEntity $fiscalEntity,
        #[ORM\ManyToOne(inversedBy: 'payments')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Expense $expense,
        #[ORM\Column(type: Types::DECIMAL, precision: 12, scale: 2)]
        private string $amount,
        #[ORM\Column(type: Types::DATE_IMMUTABLE)]
        private \DateTimeImmutable $paidOn,
        #[ORM\Column(length: 32)]
        private string $method,
        #[ORM\Column(length: 80)]
        private string $reference,
        #[ORM\Column(length: 36)]
        private string $clientToken,
    ) {
    }

    public static function record(
        Expense $expense,
        string $amount,
        \DateTimeImmutable $paidOn,
        string $method,
        string $reference,
        string $clientToken,
    ): self {
        return new self(
            Uuid::v7()->toRfc4122(),
            $expense->fiscalEntity(),
            $expense,
            $amount,
            $paidOn,
            $method,
            $reference,
            $clientToken,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function fiscalEntity(): FiscalEntity
    {
        return $this->fiscalEntity;
    }

    public function expense(): Expense
    {
        return $this->expense;
    }

    public function amount(): string
    {
        return $this->amount;
    }

    public function paidOn(): \DateTimeImmutable
    {
        return $this->paidOn;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function clientToken(): string
    {
        return $this->clientToken;
    }
}
