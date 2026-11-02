<?php

declare(strict_types=1);

namespace App\Income;

final class Euro
{
    public static function format(string $amount): string
    {
        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '-');
        if (!str_contains($amount, '.')) {
            $amount .= '.00';
        }
        [$whole, $cents] = explode('.', $amount);
        $reversed = strrev($whole);
        $groups = str_split($reversed, 3);
        $whole = strrev(implode('.', $groups));

        return ($negative ? '-' : '').$whole.','.$cents.' €';
    }
}
