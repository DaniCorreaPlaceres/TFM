<?php

declare(strict_types=1);

namespace App\Income;

final class SpanishTaxId
{
    private const string NIF_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';
    private const string CIF_LETTERS = 'JABCDEFGHI';

    public static function canonical(string $raw): string
    {
        $taxId = strtoupper(str_replace([' ', '-', '.'], '', trim($raw)));
        if (str_starts_with($taxId, 'ES') && strlen($taxId) > 2) {
            $taxId = substr($taxId, 2);
        }
        if (self::nif($taxId) || self::nie($taxId) || self::cif($taxId)) {
            return $taxId;
        }

        throw new InvalidIncome(InvalidIncome::TAX_ID);
    }

    private static function nif(string $taxId): bool
    {
        if (1 !== preg_match('/^(\d{8})([A-Z])$/', $taxId, $matches)) {
            return false;
        }

        return $matches[2] === self::NIF_LETTERS[(int) $matches[1] % 23];
    }

    private static function nie(string $taxId): bool
    {
        if (1 !== preg_match('/^([XYZ])(\d{7})([A-Z])$/', $taxId, $matches)) {
            return false;
        }
        $prefix = ['X' => '0', 'Y' => '1', 'Z' => '2'][$matches[1]];

        return $matches[3] === self::NIF_LETTERS[(int) ($prefix.$matches[2]) % 23];
    }

    private static function cif(string $taxId): bool
    {
        if (1 !== preg_match('/^([ABCDEFGHJNPQRSUVW])(\d{7})([0-9A-J])$/', $taxId, $matches)) {
            return false;
        }
        $sum = 0;
        for ($index = 0; $index < 7; ++$index) {
            $digit = (int) $matches[2][$index];
            if (0 === $index % 2) {
                $digit *= 2;
                $sum += intdiv($digit, 10) + ($digit % 10);
            } else {
                $sum += $digit;
            }
        }
        $control = (10 - ($sum % 10)) % 10;
        $digit = (string) $control;
        $letter = self::CIF_LETTERS[$control];
        $actual = $matches[3];
        if (str_contains('PQRSNW', $matches[1])) {
            return $actual === $letter;
        }
        if (str_contains('ABEH', $matches[1])) {
            return $actual === $digit;
        }

        return $actual === $digit || $actual === $letter;
    }
}
