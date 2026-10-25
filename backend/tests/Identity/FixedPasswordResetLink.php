<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\PasswordResetLinkFactory;

final class FixedPasswordResetLink implements PasswordResetLinkFactory
{
    public function url(string $token): string
    {
        return 'https://example.test/recuperar/clave/'.$token;
    }
}
