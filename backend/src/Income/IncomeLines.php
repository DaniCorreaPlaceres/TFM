<?php

declare(strict_types=1);

namespace App\Income;

final class IncomeLines
{
    /**
     * @param list<array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}> $rows
     *
     * @return list<DraftLine>
     */
    public static function parse(array $rows): array
    {
        $parsed = [];
        foreach ($rows as $row) {
            if (self::blank($row)) {
                continue;
            }
            $description = trim($row['description']);
            if ('' === $description || mb_strlen($description) > 180) {
                throw new InvalidIncome(InvalidIncome::LINE);
            }
            try {
                $values = Amount::line($row['quantity'], $row['unitPrice'], $row['taxRate'], $row['withholdingRate']);
            } catch (InvalidIncome) {
                throw new InvalidIncome(InvalidIncome::LINE);
            }
            $parsed[] = new DraftLine($description, $values);
        }
        if ([] === $parsed || count($parsed) > 50) {
            throw new InvalidIncome(InvalidIncome::LINE);
        }

        return $parsed;
    }

    /**
     * @param array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string} $row
     */
    private static function blank(array $row): bool
    {
        return '' === trim($row['description'])
            && '' === trim($row['quantity'])
            && '' === trim($row['unitPrice'])
            && '' === trim($row['taxRate'])
            && '' === trim($row['withholdingRate']);
    }
}
