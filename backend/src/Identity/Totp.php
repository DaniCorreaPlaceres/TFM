<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Component\Clock\ClockInterface;

final class Totp
{
    public function __construct(private readonly ClockInterface $clock)
    {
    }

    public function verify(#[\SensitiveParameter] string $secret, string $code, int $window = 1): bool
    {
        if (1 !== preg_match('/^\d{6}$/', $code)) {
            return false;
        }

        $step = intdiv($this->clock->now()->getTimestamp(), 30);
        for ($offset = -$window; $offset <= $window; ++$offset) {
            if (hash_equals($this->at($secret, $step + $offset), $code)) {
                return true;
            }
        }

        return false;
    }

    public function at(#[\SensitiveParameter] string $secret, int $step): string
    {
        $hash = hash_hmac('sha1', pack('N2', 0, $step), $secret, true);
        $offset = ord($hash[19]) & 0x0F;
        $unpacked = unpack('N', substr($hash, $offset, 4));
        if (false === $unpacked) {
            return '000000';
        }

        return str_pad((string) (($unpacked[1] & 0x7FFFFFFF) % 1000000), 6, '0', STR_PAD_LEFT);
    }
}
