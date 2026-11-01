<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\SodiumSecretCipher;
use PHPUnit\Framework\TestCase;

final class SodiumSecretCipherTest extends TestCase
{
    public function testElSecretoCifradoNoSeGuardaEnClaro(): void
    {
        if (!function_exists('sodium_crypto_secretbox')) {
            self::markTestSkipped('La extensión sodium no está disponible.');
        }

        $cipher = new SodiumSecretCipher('secreto-de-prueba');
        $plain = '12345678901234567890';
        $encrypted = $cipher->encrypt($plain);

        self::assertNotSame($plain, $encrypted);
        self::assertStringNotContainsString($plain, $encrypted);
        self::assertSame($plain, $cipher->decrypt($encrypted));
    }
}
