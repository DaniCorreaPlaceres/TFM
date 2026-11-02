<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class SaveExpenseCategory
{
    public function __construct(
        private readonly ExpenseAccess $access,
        private readonly ExpenseCategoryRepository $categories,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, string $name): ExpenseCategory
    {
        $this->access->holder($account, $fiscalEntity);
        $name = CategoryName::canonical($name);
        foreach ($this->categories->allForEntity($fiscalEntity) as $existing) {
            if (mb_strtolower($existing->name()) === mb_strtolower($name)) {
                throw new InvalidExpense(InvalidExpense::EXISTS);
            }
        }
        $category = ExpenseCategory::open($fiscalEntity, $name, false, $this->clock->now());
        $this->store->commit(function () use ($category): void {
            $this->categories->save($category);
        });

        return $category;
    }
}
