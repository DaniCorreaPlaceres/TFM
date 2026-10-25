<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\PwnedPasswordList;
use App\Identity\PwnedPasswordLookupFailed;

final class ConfigurablePwnedPasswordList implements PwnedPasswordList
{
    /** @var list<string> */
    public static array $compromised = [];

    public static bool $fail = false;

    public static int $calls = 0;

    public static function reset(): void
    {
        self::$compromised = [];
        self::$fail = false;
        self::$calls = 0;
    }

    public function contains(string $password): bool
    {
        ++self::$calls;
        if (self::$fail) {
            throw new PwnedPasswordLookupFailed();
        }

        return in_array($password, self::$compromised, true);
    }
}
