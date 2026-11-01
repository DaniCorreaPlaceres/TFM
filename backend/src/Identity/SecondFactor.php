<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\RecoveryCode;
use Symfony\Component\Clock\ClockInterface;

final class SecondFactor
{
    public const int RECOVERY_CODE_COUNT = 8;

    private const string ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(
        private readonly Totp $totp,
        private readonly SecretCipher $cipher,
        private readonly RecoveryCodeRepository $codes,
        private readonly AccountRepository $accounts,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function newSecret(): string
    {
        return random_bytes(20);
    }

    /**
     * @return array{secret: string, uri: string}
     */
    public function provisioning(string $email, #[\SensitiveParameter] string $secret): array
    {
        $encoded = Base32::encode($secret);

        return [
            'secret' => $encoded,
            'uri' => 'otpauth://totp/Aselto:'.rawurlencode($email).'?secret='.$encoded.'&issuer=Aselto&algorithm=SHA1&digits=6&period=30',
        ];
    }

    /**
     * @return list<string>
     */
    public function confirm(Account $account, #[\SensitiveParameter] string $secret, string $code): array
    {
        if (!$this->totp->verify($secret, $code)) {
            throw new InvalidSecondFactor(InvalidSecondFactor::CODE);
        }

        $now = $this->clock->now();
        $plain = [];
        for ($index = 0; $index < self::RECOVERY_CODE_COUNT; ++$index) {
            $plain[] = $this->plainCode();
        }

        $this->store->commit(function () use ($account, $secret, $plain, $now): void {
            $account->enableTotp($this->cipher->encrypt($secret));
            $this->accounts->save($account);
            foreach ($plain as $value) {
                $this->codes->save(RecoveryCode::issue($account, $this->hash($value), $now));
            }
        });

        return $plain;
    }

    public function verify(Account $account, string $code): void
    {
        $encrypted = $account->totpSecret();
        if (null === $encrypted) {
            throw new InvalidSecondFactor(InvalidSecondFactor::CODE);
        }

        $digits = preg_replace('/\s+/', '', $code) ?? '';
        if (1 === preg_match('/^\d{6}$/', $digits) && $this->matchesTotp($encrypted, $digits)) {
            return;
        }

        $recovery = $this->codes->findUsable($account, $this->hash($code));
        if (null === $recovery) {
            throw new InvalidSecondFactor(InvalidSecondFactor::CODE);
        }

        $this->store->commit(function () use ($recovery): void {
            $recovery->use($this->clock->now());
            $this->codes->save($recovery);
        });
    }

    public function disable(Account $account, string $code): void
    {
        $this->verify($account, $code);
        $this->store->commit(function () use ($account): void {
            $account->disableTotp();
            $this->accounts->save($account);
            $this->codes->deleteFor($account);
        });
    }

    private function matchesTotp(string $encrypted, string $code): bool
    {
        try {
            return $this->totp->verify($this->cipher->decrypt($encrypted), $code);
        } catch (\Throwable) {
            return false;
        }
    }

    private function plainCode(): string
    {
        $raw = '';
        $last = strlen(self::ALPHABET) - 1;
        for ($index = 0; $index < 8; ++$index) {
            $raw .= self::ALPHABET[random_int(0, $last)];
        }

        return substr($raw, 0, 4).'-'.substr($raw, 4);
    }

    private function hash(string $code): string
    {
        $normalized = strtoupper(str_replace(['-', ' '], '', trim($code)));

        return hash('sha256', $normalized);
    }
}
