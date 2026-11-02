<?php

declare(strict_types=1);

namespace App\Income;

use Symfony\Component\HttpFoundation\Request;

final class PostedLines
{
    /**
     * @return list<array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}>
     */
    public static function from(Request $request): array
    {
        $descriptions = self::column($request, 'descripcion');
        $quantities = self::column($request, 'cantidad');
        $prices = self::column($request, 'precio');
        $taxes = self::column($request, 'iva');
        $withholdings = self::column($request, 'retencion');
        $count = max(count($descriptions), count($quantities), count($prices), count($taxes), count($withholdings));
        $rows = [];
        for ($index = 0; $index < $count; ++$index) {
            $rows[] = [
                'description' => $descriptions[$index] ?? '',
                'quantity' => $quantities[$index] ?? '',
                'unitPrice' => $prices[$index] ?? '',
                'taxRate' => $taxes[$index] ?? '',
                'withholdingRate' => $withholdings[$index] ?? '',
            ];
        }

        return $rows;
    }

    /**
     * @return list<string>
     */
    private static function column(Request $request, string $name): array
    {
        $values = $request->request->all($name);
        $column = [];
        foreach ($values as $value) {
            $column[] = is_string($value) ? $value : '';
        }

        return $column;
    }
}
