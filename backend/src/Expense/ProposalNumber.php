<?php

declare(strict_types=1);

namespace App\Expense;

final class ProposalNumber
{
    public static function from(string $documentNo): string
    {
        $suffix = '-P';
        $base = mb_substr($documentNo, 0, 40 - mb_strlen($suffix));

        return DocumentNumber::canonical($base.$suffix);
    }
}
