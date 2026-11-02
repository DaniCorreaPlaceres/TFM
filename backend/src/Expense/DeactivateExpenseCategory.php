<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;

final class DeactivateExpenseCategory
{
    public function __construct(
        private readonly ExpenseAccess $access,
        private readonly ExpenseCategoryRepository $categories,
        private readonly RegistrationStore $store,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, string $categoryId): void
    {
        $this->access->holder($account, $fiscalEntity);
        $category = $this->categories->findForEntity($fiscalEntity, $categoryId);
        if (null === $category) {
            throw new InvalidExpense(InvalidExpense::FORBIDDEN);
        }
        $category->deactivate();
        $this->store->commit(function () use ($category): void {
            $this->categories->save($category);
        });
    }
}
