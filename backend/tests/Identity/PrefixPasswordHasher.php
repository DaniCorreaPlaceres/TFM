<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class PrefixPasswordHasher implements PasswordHasherInterface
{
    public function hash(string $plainPassword): string
    {
        return 'hashed:'.$plainPassword;
    }

    public function verify(string $hashedPassword, string $plainPassword): bool
    {
        return hash_equals($hashedPassword, 'hashed:'.$plainPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return false;
    }
}
