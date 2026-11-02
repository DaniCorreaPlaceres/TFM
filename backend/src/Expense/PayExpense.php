<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;

final class PayExpense
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
        string $amount,
        \DateTimeImmutable $paidOn,
        string $method,
        string $reference,
        string $token,
    ): void {
        $this->access->holder($account, $fiscalEntity);
        $expense = $this->expenses->findForEntity($fiscalEntity, $expenseId);
        if (null === $expense) {
            throw new InvalidExpense(InvalidExpense::FORBIDDEN);
        }
        if (null !== $expense->findPayment($token)) {
            return;
        }
        $expense->pay($amount, $paidOn, $method, $reference, $token);
        $this->store->commit(function () use ($expense): void {
            $this->expenses->save($expense);
        });
    }
}
