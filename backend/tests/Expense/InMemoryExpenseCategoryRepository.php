<?php

declare(strict_types=1);

namespace App\Tests\Expense;

use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use App\Expense\ExpenseCategoryRepository;

final class InMemoryExpenseCategoryRepository implements ExpenseCategoryRepository
{
    /** @var array<string, ExpenseCategory> */
    private array $categories = [];

    public function save(ExpenseCategory $category): void
    {
        $this->categories[$category->id()] = $category;
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?ExpenseCategory
    {
        $category = $this->categories[$id] ?? null;
        if (null === $category || !$category->belongsTo($fiscalEntity)) {
            return null;
        }

        return $category;
    }

    public function allForEntity(FiscalEntity $fiscalEntity): array
    {
        $categories = [];
        foreach ($this->categories as $category) {
            if ($category->belongsTo($fiscalEntity)) {
                $categories[] = $category;
            }
        }
        usort($categories, static fn (ExpenseCategory $left, ExpenseCategory $right): int => $left->name() <=> $right->name());

        return $categories;
    }
}
