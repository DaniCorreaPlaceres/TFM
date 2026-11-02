<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;

final class ReviewExpense
{
    public function __construct(
        private readonly ExpenseAccess $access,
        private readonly ExpenseRepository $expenses,
        private readonly RegistrationStore $store,
    ) {
    }

    public function __invoke(
        Account $account,
        FiscalEntity $fiscalEntity,
        string $expenseId,
        string $state,
        string $reason,
    ): void {
        $this->access->holder($account, $fiscalEntity);
        $expense = $this->expenses->findForEntity($fiscalEntity, $expenseId);
        if (null === $expense) {
            throw new InvalidExpense(InvalidExpense::FORBIDDEN);
        }
        $expense->review($state, $reason);
        $this->store->commit(function () use ($expense): void {
            $this->expenses->save($expense);
        });
    }
}
