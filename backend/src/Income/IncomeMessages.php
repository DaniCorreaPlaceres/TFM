<?php

declare(strict_types=1);

namespace App\Income;

final class IncomeMessages
{
    public static function of(string $reason): string
    {
        return match ($reason) {
            InvalidIncome::NAME => 'El nombre es obligatorio y no puede pasar de 180 caracteres.',
            InvalidIncome::TAX_ID => 'El NIF no es válido.',
            InvalidIncome::ROLE => 'El contacto tiene que ser cliente, proveedor o ambos.',
            InvalidIncome::EXISTS => 'Ya existe un contacto con ese NIF.',
            InvalidIncome::CONTACT => 'Elige un cliente de esta entidad.',
            InvalidIncome::DATE => 'La fecha no es válida.',
            InvalidIncome::DUE => 'El vencimiento no puede ser anterior a la fecha.',
            InvalidIncome::INVOICE => 'El número no es válido.',
            InvalidIncome::LINE => 'Revisa las líneas: hace falta una descripción y cifras válidas.',
            InvalidIncome::AMOUNT => 'El importe no es válido.',
            InvalidIncome::EXCEEDS => 'El cobro supera el saldo pendiente.',
            InvalidIncome::BELOW_PAYMENTS => 'El nuevo total no puede quedar por debajo de los cobros ya registrados.',
            InvalidIncome::DUPLICATE => 'Ya existe un ingreso con este número, NIF y fecha.',
            InvalidIncome::CLOSED => 'Este trimestre está cerrado.',
            InvalidIncome::STALE => 'Este ingreso ha cambiado. Vuelve a abrirlo.',
            InvalidIncome::ANNULLED => 'El ingreso está anulado.',
            InvalidIncome::REASON => 'Escribe el motivo de la anulación.',
            InvalidIncome::FILE => 'El archivo tiene que ser un PDF o una imagen de hasta 8 MB.',
            InvalidIncome::METHOD => 'El método de cobro no es válido.',
            InvalidIncome::TOKEN => 'No se ha podido registrar el cobro.',
            InvalidIncome::PERIOD => 'El trimestre no es válido.',
            InvalidIncome::ALREADY_CLOSED => 'Ese trimestre ya está cerrado.',
            default => 'No se ha podido guardar.',
        };
    }
}
