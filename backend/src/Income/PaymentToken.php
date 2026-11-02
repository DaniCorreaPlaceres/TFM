<?php

declare(strict_types=1);

namespace App\Income;

final class PaymentToken
{
    public static function assert(string $token): void
    {
        if (1 !== preg_match('/^[0-9a-fA-F-]{36}$/', $token)) {
            throw new InvalidIncome(InvalidIncome::TOKEN);
        }
    }
}
