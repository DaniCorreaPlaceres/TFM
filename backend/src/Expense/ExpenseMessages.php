<?php

declare(strict_types=1);

namespace App\Expense;

final class ExpenseMessages
{
    public static function of(string $reason): string
    {
        return match ($reason) {
            InvalidExpense::CONTACT => 'Elige un proveedor de esta entidad.',
            InvalidExpense::CATEGORY => 'Elige una categoría activa.',
            InvalidExpense::KIND => 'El tipo de gasto no es válido.',
            InvalidExpense::METHOD => 'El método de pago no es válido.',
            InvalidExpense::DOCUMENT => 'El número del documento no es válido.',
            InvalidExpense::DATE => 'La fecha no es válida.',
            InvalidExpense::LINE => 'Revisa las líneas. Hace falta al menos una, con descripción e importes válidos.',
            InvalidExpense::NAME => 'El nombre de la categoría no es válido.',
            InvalidExpense::EXISTS => 'Esa categoría ya existe en la entidad.',
            InvalidExpense::PART => 'Indica si la decisión es de la base o de la cuota, y si es deducible.',
            InvalidExpense::REASON => 'Escribe el motivo de la deducibilidad.',
            default => 'No se ha podido guardar el gasto.',
        };
    }
}
