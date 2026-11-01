<?php

declare(strict_types=1);

namespace App\Identity;

final class Base32
{
    private const string ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function encode(string $bytes): string
    {
        $bits = '';
        $length = strlen($bytes);
        for ($index = 0; $index < $length; ++$index) {
            $bits .= str_pad(decbin(ord($bytes[$index])), 8, '0', STR_PAD_LEFT);
        }

        $encoded = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (5 !== strlen($chunk)) {
                $chunk = str_pad($chunk, 5, '0');
            }
            $encoded .= self::ALPHABET[bindec($chunk)];
        }

        return $encoded;
    }

    public static function decode(string $encoded): string
    {
        $bits = '';
        $encoded = strtoupper(rtrim($encoded, '='));
        $length = strlen($encoded);
        for ($index = 0; $index < $length; ++$index) {
            $position = strpos(self::ALPHABET, $encoded[$index]);
            if (false === $position) {
                throw new \InvalidArgumentException('Secreto no válido.');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $decoded = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (8 !== strlen($chunk)) {
                continue;
            }
            $decoded .= chr(bindec($chunk));
        }

        return $decoded;
    }
}
