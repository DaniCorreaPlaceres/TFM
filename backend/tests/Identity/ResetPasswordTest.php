<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\InvalidPasswordReset;
use App\Identity\InvalidRegistration;
use App\Identity\PasswordPolicy;
use App\Identity\RequestPasswordReset;
use App\Identity\ResetPassword;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ResetPasswordTest extends TestCase
{
    public function testElAvisoNoEnviaCorreoSiNoHayCuentaConfirmada(): void
    {
        $fixture = $this->fixture();
        $fixture['request']('nadie@example.com');
        $account = Account::register('persona@example.com', 'hashed:clave-local-segura', $fixture['clock']->now());
        $fixture['accounts']->save($account);
        $fixture['request']('persona@example.com');

        self::assertSame([], RecordingAccountMailer::$messages);
    }

    public function testElEnlaceDuraTreintaMinutosSirveUnaVezYElAnteriorDejaDeValer(): void
    {
        $fixture = $this->fixture();
        $account = $this->confirmed($fixture);
        $fixture['request']($account->email());
        $first = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $fixture['request']($account->email());
        $second = $this->tokenFrom(RecordingAccountMailer::$messages[1]['url']);

        self::assertFalse($fixture['reset']->usable($first));
        $fixture['clock']->modify('+29 minutes');
        $fixture['reset']($second, 'clave-local-nueva1');
        self::assertSame('1', $account->sessionMarker());
        self::assertTrue($fixture['hasher']->verify($account->passwordHash(), 'clave-local-nueva1'));

        $fixture['clock']->modify('+2 minutes');
        self::assertFalse($fixture['reset']->usable($second));
        try {
            $fixture['reset']($second, 'otra-clave-local-2');
            self::fail('El enlace usado tenía que rechazarse.');
        } catch (InvalidPasswordReset) {
            self::assertTrue($fixture['hasher']->verify($account->passwordHash(), 'clave-local-nueva1'));
        }
    }

    public function testElEnlaceCaducaALosTreintaMinutos(): void
    {
        $fixture = $this->fixture();
        $account = $this->confirmed($fixture);
        $fixture['request']($account->email());
        $token = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $fixture['clock']->modify('+30 minutes');

        self::assertFalse($fixture['reset']->usable($token));
        $this->expectException(InvalidPasswordReset::class);
        $fixture['reset']($token, 'clave-local-nueva1');
    }

    public function testUnaContrasenaCortaNoGastaElEnlace(): void
    {
        $fixture = $this->fixture();
        $account = $this->confirmed($fixture);
        $fixture['request']($account->email());
        $token = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);

        try {
            $fixture['reset']($token, 'corta');
            self::fail('La contraseña corta tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::SHORT, $exception->reason);
        }

        self::assertTrue($fixture['reset']->usable($token));
        self::assertSame('0', $account->sessionMarker());
    }

    public function testLaSextaSolicitudNoEnviaOtroEnlace(): void
    {
        $fixture = $this->fixture();
        $account = $this->confirmed($fixture);
        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            $fixture['request']($account->email());
        }
        self::assertCount(5, RecordingAccountMailer::$messages);

        $fixture['request']($account->email());
        self::assertCount(5, RecordingAccountMailer::$messages);
    }

    /**
     * @return array{
     *     accounts: InMemoryAccountRepository,
     *     clock: MockClock,
     *     hasher: PrefixPasswordHasher,
     *     request: RequestPasswordReset,
     *     reset: ResetPassword
     * }
     */
    private function fixture(): array
    {
        ConfigurablePwnedPasswordList::reset();
        RecordingAccountMailer::reset();
        $accounts = new InMemoryAccountRepository();
        $resets = new InMemoryPasswordResetRepository();
        $clock = new MockClock('2026-10-25 18:00:00 UTC');
        $hasher = new PrefixPasswordHasher();
        $store = new InMemoryRegistrationStore();
        $request = new RequestPasswordReset(
            $accounts,
            $resets,
            $store,
            new RecordingAccountMailer(),
            new FixedPasswordResetLink(),
            $clock,
            new InMemoryAttemptLimiter(),
        );
        $reset = new ResetPassword(
            $resets,
            $accounts,
            $store,
            $hasher,
            new PasswordPolicy(new ConfigurablePwnedPasswordList()),
            $clock,
        );

        return compact('accounts', 'clock', 'hasher', 'request', 'reset');
    }

    /**
     * @param array{accounts: InMemoryAccountRepository, clock: MockClock} $fixture
     */
    private function confirmed(array $fixture): Account
    {
        $account = Account::register('persona@example.com', 'hashed:clave-local-segura', $fixture['clock']->now());
        $account->verify($fixture['clock']->now());
        $fixture['accounts']->save($account);

        return $account;
    }

    private function tokenFrom(string $url): string
    {
        $token = basename($url);
        self::assertSame(64, strlen($token));

        return $token;
    }
}
