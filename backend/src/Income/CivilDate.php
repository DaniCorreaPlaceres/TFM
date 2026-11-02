<?php

declare(strict_types=1);

namespace App\Income;

final class CivilDate
{
    public static function parse(string $raw): ?\DateTimeImmutable
    {
        $raw = trim($raw);
        foreach (['!Y-m-d', '!d/m/Y'] as $format) {
            $date = \DateTimeImmutable::createFromFormat($format, $raw);
            $errors = \DateTimeImmutable::getLastErrors();
            if ($date instanceof \DateTimeImmutable && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))) {
                return $date;
            }
        }

        return null;
    }
}
