<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class SodiumSecretCipher implements SecretCipher
{
    public function __construct(
        #[Autowire('%kernel.secret%')]
        private readonly string $appSecret,
    ) {
    }

    public function encrypt(#[\SensitiveParameter] string $plain): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox($plain, $nonce, $this->key());

        return sodium_bin2base64($nonce.$cipher, SODIUM_BASE64_VARIANT_ORIGINAL);
    }

    public function decrypt(string $payload): string
    {
        $raw = sodium_base642bin($payload, SODIUM_BASE64_VARIANT_ORIGINAL);
        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $this->key());
        if (false === $plain) {
            throw new \RuntimeException('No se pudo leer el secreto.');
        }

        return $plain;
    }

    private function key(): string
    {
        return sodium_crypto_generichash($this->appSecret, '', SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
    }
}
