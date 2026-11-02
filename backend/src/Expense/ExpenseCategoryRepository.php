<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;

interface ExpenseCategoryRepository
{
    public function save(ExpenseCategory $category): void;

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?ExpenseCategory;

    /**
     * @return list<ExpenseCategory>
     */
    public function allForEntity(FiscalEntity $fiscalEntity): array;
}
