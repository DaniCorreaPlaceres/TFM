<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Income\ObjectStore;

final class MemoryObjectStore implements ObjectStore
{
    /** @var array<string, string> */
    public static array $objects = [];

    public static function reset(): void
    {
        self::$objects = [];
    }

    public function put(string $key, string $body): void
    {
        self::$objects[$key] = $body;
    }

    public function get(string $key): string
    {
        if (!isset(self::$objects[$key])) {
            throw new \RuntimeException('No se ha podido leer el archivo.');
        }

        return self::$objects[$key];
    }
}
