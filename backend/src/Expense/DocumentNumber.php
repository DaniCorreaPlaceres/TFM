<?php

declare(strict_types=1);

namespace App\Expense;

final class DocumentNumber
{
    public static function canonical(string $raw): string
    {
        $number = trim($raw);
        if ('' === $number || mb_strlen($number) > 40 || 1 !== preg_match('/^[\p{L}\p{N}\-_\/. ]+$/u', $number)) {
            throw new InvalidExpense(InvalidExpense::DOCUMENT);
        }

        return $number;
    }
}
