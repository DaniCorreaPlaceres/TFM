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
            InvalidExpense::AMOUNT => 'El importe del pago no es válido.',
            InvalidExpense::EXCEEDS => 'El pago supera el saldo pendiente.',
            InvalidExpense::TOKEN => 'El identificador del pago no es válido.',
            InvalidExpense::REFERENCE => 'La referencia del pago no es válida.',
            InvalidExpense::STATE => 'Esa transición de revisión no es válida.',
            InvalidExpense::REVIEW_REASON => 'Escribe el motivo de la revisión.',
            InvalidExpense::FROZEN => 'Este gasto ya no admite cambios.',
            default => 'No se ha podido guardar el gasto.',
        };
    }
}
