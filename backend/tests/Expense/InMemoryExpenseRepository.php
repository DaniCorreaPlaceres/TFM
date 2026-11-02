<?php

declare(strict_types=1);

namespace App\Tests\Expense;

use App\Entity\Expense;
use App\Entity\FiscalEntity;
use App\Expense\ExpenseRepository;

final class InMemoryExpenseRepository implements ExpenseRepository
{
    /** @var array<string, Expense> */
    private array $expenses = [];

    public function save(Expense $expense): void
    {
        $this->expenses[$expense->id()] = $expense;
    }

    public function forEntity(FiscalEntity $fiscalEntity): array
    {
        $expenses = [];
        foreach ($this->expenses as $expense) {
            if ($expense->belongsTo($fiscalEntity)) {
                $expenses[] = $expense;
            }
        }

        return $expenses;
    }
}
