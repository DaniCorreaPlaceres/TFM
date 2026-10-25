<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\AuthenticateAccount;
use App\Identity\InvalidLogin;
use PHPUnit\Framework\TestCase;

final class AuthenticateAccountTest extends TestCase
{
    public function testUnaCuentaConfirmadaEntra(): void
    {
        $accounts = $this->accounts();
        $account = $this->account($accounts, true);

        $authenticated = $this->authenticate($accounts)('persona@example.com', 'clave-local-segura');

        self::assertSame($account->id(), $authenticated->id());
    }

    public function testUnaCuentaSinConfirmarNoEntra(): void
    {
        $accounts = $this->accounts();
        $this->account($accounts, false);

        try {
            $this->authenticate($accounts)('persona@example.com', 'clave-local-segura');
            self::fail('La cuenta sin confirmar tenía que rechazarse.');
        } catch (InvalidLogin $exception) {
            self::assertSame(InvalidLogin::UNVERIFIED, $exception->reason);
        }
    }

    public function testUnCorreoDesconocidoYUnaContrasenaIncorrectaSeRechazanIgual(): void
    {
        $accounts = $this->accounts();
        $this->account($accounts, true);
        $authenticate = $this->authenticate($accounts);

        try {
            $authenticate('nadie@example.com', 'clave-local-segura');
            self::fail('El correo desconocido tenía que rechazarse.');
        } catch (InvalidLogin $unknown) {
            self::assertSame(InvalidLogin::WRONG, $unknown->reason);
        }

        try {
            $authenticate('persona@example.com', 'clave-equivocada');
            self::fail('La contraseña incorrecta tenía que rechazarse.');
        } catch (InvalidLogin $wrong) {
            self::assertSame(InvalidLogin::WRONG, $wrong->reason);
        }
    }

    public function testElSextoIntentoFallidoQuedaBloqueado(): void
    {
        $accounts = $this->accounts();
        $this->account($accounts, true);
        $authenticate = $this->authenticate($accounts);

        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            try {
                $authenticate('persona@example.com', 'clave-equivocada');
                self::fail('El intento fallido tenía que rechazarse.');
            } catch (InvalidLogin $exception) {
                self::assertSame(InvalidLogin::WRONG, $exception->reason);
            }
        }

        try {
            $authenticate('persona@example.com', 'clave-local-segura');
            self::fail('El sexto intento tenía que bloquearse.');
        } catch (InvalidLogin $exception) {
            self::assertSame(InvalidLogin::TOO_MANY, $exception->reason);
        }
    }

    private function authenticate(InMemoryAccountRepository $accounts): AuthenticateAccount
    {
        return new AuthenticateAccount($accounts, new PrefixPasswordHasher(), new InMemoryAttemptLimiter());
    }

    private function accounts(): InMemoryAccountRepository
    {
        return new InMemoryAccountRepository();
    }

    private function account(InMemoryAccountRepository $accounts, bool $verified): Account
    {
        $now = new \DateTimeImmutable('2026-10-25 18:00:00 UTC');
        $account = Account::register('persona@example.com', 'hashed:clave-local-segura', $now);
        if ($verified) {
            $account->verify($now);
        }
        $accounts->save($account);

        return $account;
    }
}
