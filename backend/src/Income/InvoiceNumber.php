<?php

declare(strict_types=1);

namespace App\Income;

final class InvoiceNumber
{
    public static function canonical(string $raw): string
    {
        $invoice = trim($raw);
        if (mb_strlen($invoice) > 40) {
            throw new InvalidIncome(InvalidIncome::INVOICE);
        }
        if ('' !== $invoice && 1 !== preg_match('/^[\p{L}\p{N}\-_\/. ]+$/u', $invoice)) {
            throw new InvalidIncome(InvalidIncome::INVOICE);
        }

        return $invoice;
    }
}
