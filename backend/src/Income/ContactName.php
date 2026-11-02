<?php

declare(strict_types=1);

namespace App\Income;

final class ContactName
{
    public static function canonical(string $raw): string
    {
        $name = trim($raw);
        if ('' === $name || mb_strlen($name) > 180 || 1 === preg_match('/[\x00-\x1F\x7F]/', $name)) {
            throw new InvalidIncome(InvalidIncome::NAME);
        }

        return $name;
    }
}
