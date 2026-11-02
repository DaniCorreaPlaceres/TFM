<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Income\IncomeRepository;

final class InMemoryIncomeRepository implements IncomeRepository
{
    /** @var array<string, Income> */
    private array $incomes = [];

    public function save(Income $income): void
    {
        $this->incomes[$income->id()] = $income;
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Income
    {
        $income = $this->incomes[$id] ?? null;
        if (null === $income || $income->fiscalEntity()->id() !== $fiscalEntity->id()) {
            return null;
        }

        return $income;
    }

    public function findDuplicate(
        FiscalEntity $fiscalEntity,
        string $invoiceNo,
        string $taxId,
        \DateTimeImmutable $issuedOn,
        ?string $exceptId,
    ): ?Income {
        if ('' === $invoiceNo) {
            return null;
        }
        foreach ($this->incomes as $income) {
            if ($income->id() === $exceptId) {
                continue;
            }
            if ($income->fiscalEntity()->id() !== $fiscalEntity->id()) {
                continue;
            }
            if ($income->invoiceNo() === $invoiceNo && $income->snapshotTaxId() === $taxId && $income->issuedOn()->format('Y-m-d') === $issuedOn->format('Y-m-d')) {
                return $income;
            }
        }

        return null;
    }

    public function search(FiscalEntity $fiscalEntity, string $query): array
    {
        $found = [];
        foreach ($this->incomes as $income) {
            if ($income->fiscalEntity()->id() !== $fiscalEntity->id()) {
                continue;
            }
            if ('' === trim($query) || str_contains($income->invoiceNo(), $query) || str_contains($income->snapshotName(), $query) || str_contains($income->snapshotTaxId(), strtoupper($query))) {
                $found[] = $income;
            }
        }

        return $found;
    }
}
