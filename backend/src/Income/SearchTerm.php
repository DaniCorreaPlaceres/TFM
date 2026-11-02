<?php

declare(strict_types=1);

namespace App\Income;

final class SearchTerm
{
    public static function like(string $query): string
    {
        $query = mb_substr(trim($query), 0, 80);
        $query = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);

        return '%'.$query.'%';
    }
}
