<?php

declare(strict_types=1);

namespace App\Expense;

final class DefaultExpenseCategories
{
    /** @var list<string> */
    public const array NAMES = [
        'Suministros',
        'Alquiler',
        'Servicios profesionales',
        'Material',
        'Transporte',
        'Otros',
    ];
}
