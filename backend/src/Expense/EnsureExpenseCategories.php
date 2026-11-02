<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class EnsureExpenseCategories
{
    public function __construct(
        private readonly ExpenseCategoryRepository $categories,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(FiscalEntity $fiscalEntity): void
    {
        if ([] !== $this->categories->allForEntity($fiscalEntity)) {
            return;
        }
        $createdAt = $this->clock->now();
        $this->store->commit(function () use ($fiscalEntity, $createdAt): void {
            foreach (DefaultExpenseCategories::NAMES as $name) {
                $this->categories->save(ExpenseCategory::open($fiscalEntity, $name, true, $createdAt));
            }
        });
    }
}
