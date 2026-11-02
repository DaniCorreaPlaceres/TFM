<?php

declare(strict_types=1);

namespace App\Expense;

final class CategoryName
{
    public static function canonical(string $raw): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $raw) ?? '');
        if ('' === $name || mb_strlen($name) > 60) {
            throw new InvalidExpense(InvalidExpense::NAME);
        }

        return $name;
    }
}
