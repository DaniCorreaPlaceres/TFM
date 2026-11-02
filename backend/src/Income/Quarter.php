<?php

declare(strict_types=1);

namespace App\Income;

final class Quarter
{
    /**
     * @return array{0: int, 1: int}
     */
    public static function of(\DateTimeImmutable $date): array
    {
        return [(int) $date->format('Y'), intdiv((int) $date->format('n') - 1, 3) + 1];
    }
}
