<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Expense;
use App\Entity\FiscalEntity;

interface ExpenseRepository
{
    public function save(Expense $expense): void;

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Expense;

    /**
     * @return list<Expense>
     */
    public function forEntity(FiscalEntity $fiscalEntity): array;
}
