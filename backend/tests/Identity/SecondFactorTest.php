<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\InvalidSecondFactor;
use App\Identity\SecondFactor;
use App\Identity\SecretCipher;
use App\Identity\Totp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class SecondFactorTest extends TestCase
{
    public function testUnCodigoIncorrectoNoActivaElSegundoFactor(): void
    {
        [$factor, $account] = $this->factor();

        try {
            $factor->confirm($account, '12345678901234567890', '000000');
            self::fail('El código incorrecto no debe activar el segundo factor.');
        } catch (InvalidSecondFactor $exception) {
            self::assertSame(InvalidSecondFactor::CODE, $exception->reason);
        }

        self::assertFalse($account->requiresSecondFactor());
    }

    public function testActivaConTotpGuardaOchoCodigosYCadaUnoSirveUnaVez(): void
    {
        [$factor, $account] = $this->factor();
        $secret = '12345678901234567890';

        $codes = $factor->confirm($account, $secret, '050471');

        self::assertCount(SecondFactor::RECOVERY_CODE_COUNT, $codes);
        self::assertCount(SecondFactor::RECOVERY_CODE_COUNT, array_unique($codes));
        self::assertTrue($account->requiresSecondFactor());
        self::assertNotSame($secret, $account->totpSecret());
        foreach ($codes as $code) {
            self::assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$/', $code);
            self::assertStringNotContainsString($code, (string) $account->totpSecret());
        }

        $factor->verify($account, '050471');
        $factor->verify($account, strtolower($codes[0]));
        try {
            $factor->verify($account, $codes[0]);
            self::fail('Un código de recuperación usado no debe volver a servir.');
        } catch (InvalidSecondFactor) {
            self::assertTrue($account->requiresSecondFactor());
        }

        $factor->disable($account, $codes[1]);
        self::assertFalse($account->requiresSecondFactor());
        self::assertNull($account->totpSecret());

        try {
            $factor->verify($account, $codes[2]);
            self::fail('Al desactivar, los códigos de recuperación dejan de servir.');
        } catch (InvalidSecondFactor) {
            self::assertFalse($account->requiresSecondFactor());
        }
    }

    /**
     * @return array{SecondFactor, Account}
     */
    private function factor(): array
    {
        $clock = new MockClock('@1111111111');
        $accounts = new InMemoryAccountRepository();
        $account = Account::register('persona@example.com', 'hash', $clock->now());
        $account->verify($clock->now());
        $accounts->save($account);
        $cipher = new class implements SecretCipher {
            public function encrypt(string $plain): string
            {
                return 'v1:'.$plain;
            }

            public function decrypt(string $payload): string
            {
                return substr($payload, 3);
            }
        };
        $factor = new SecondFactor(
            new Totp($clock),
            $cipher,
            new InMemoryRecoveryCodeRepository(),
            $accounts,
            new InMemoryRegistrationStore(),
            $clock,
        );

        return [$factor, $account];
    }
}
