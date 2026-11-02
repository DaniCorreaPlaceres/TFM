<?php

declare(strict_types=1);

namespace App\Expense;

final class PaymentMethod
{
    /**
     * @return array<string, string>
     */
    public static function labels(): array
    {
        return [
            'transferencia' => 'Transferencia',
            'tarjeta' => 'Tarjeta',
            'efectivo' => 'Efectivo',
            'domiciliacion' => 'Domiciliación',
            'otro' => 'Otro',
        ];
    }

    public static function canonical(string $raw): string
    {
        $method = strtolower(trim($raw));
        if (!isset(self::labels()[$method])) {
            throw new InvalidExpense(InvalidExpense::METHOD);
        }

        return $method;
    }

    public static function label(string $method): string
    {
        return self::labels()[$method] ?? $method;
    }
}
