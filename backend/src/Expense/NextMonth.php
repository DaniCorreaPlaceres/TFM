<?php

declare(strict_types=1);

namespace App\Expense;

final class NextMonth
{
    public static function of(\DateTimeImmutable $date): \DateTimeImmutable
    {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('n') + 1;
        if ($month > 12) {
            $month = 1;
            ++$year;
        }
        $day = (int) $date->format('j');
        $last = (int) (new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month)))->format('t');
        if ($day > $last) {
            $day = $last;
        }

        return new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
    }
}
