<?php

declare(strict_types=1);

namespace App\Income;

interface ObjectStore
{
    public function put(string $key, string $body): void;

    public function get(string $key): string;
}
