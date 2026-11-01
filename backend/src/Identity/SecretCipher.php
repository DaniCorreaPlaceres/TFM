<?php

declare(strict_types=1);

namespace App\Identity;

interface SecretCipher
{
    public function encrypt(#[\SensitiveParameter] string $plain): string;

    public function decrypt(string $payload): string;
}
