<?php

declare(strict_types=1);

namespace App\Expense;

final class ExpenseKind
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'factura' => 'Factura',
            'ticket' => 'Ticket',
            'otro' => 'Otro gasto',
        ];
    }

    public static function canonical(string $raw): string
    {
        $kind = strtolower(trim($raw));
        if (!isset(self::labels()[$kind])) {
            throw new InvalidExpense(InvalidExpense::KIND);
        }

        return $kind;
    }

    public static function label(string $kind): string
    {
        return self::labels()[$kind] ?? $kind;
    }
}
