<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\ConfirmEmail;
use App\Identity\InvalidConfirmation;
use App\Identity\InvalidRegistration;
use App\Identity\RegisterAccount;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class RegisterAccountTest extends TestCase
{
    public function testUnaCuentaNuevaNoAccedeAInformacionFiscalHastaConfirmar(): void
    {
        $fixture = $this->fixture();
        $account = $fixture->register('Persona@Example.com', 'clave-local-segura');

        self::assertNotSame('', $account->id());
        self::assertEquals(new \DateTimeImmutable('2026-10-25 16:00:00 UTC'), $account->createdAt());
        self::assertSame('persona@example.com', $account->email());
        self::assertNotSame('clave-local-segura', $account->passwordHash());
        self::assertFalse($account->allowsFiscalAccess());
        self::assertCount(1, RecordingAccountMailer::$messages);
        self::assertSame('persona@example.com', RecordingAccountMailer::$messages[0]['to']);

        $token = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $fixture->clock->modify('+29 minutes');
        $confirmed = $fixture->confirm($token);

        self::assertTrue($confirmed->allowsFiscalAccess());
    }

    public function testUnaContrasenaCortaNoConsultaLaListaNiCreaCuenta(): void
    {
        $fixture = $this->fixture();

        try {
            $fixture->register('persona@example.com', 'corta');
            self::fail('La contraseña corta tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::SHORT, $exception->reason);
        }

        self::assertSame(0, ConfigurablePwnedPasswordList::$calls);
        self::assertNull($fixture->accounts->findByEmail('persona@example.com'));
        self::assertSame([], RecordingAccountMailer::$messages);
    }

    public function testUnaContrasenaFiltradaNoCreaCuenta(): void
    {
        $fixture = $this->fixture();
        ConfigurablePwnedPasswordList::$compromised[] = 'clave-filtrada-12';

        try {
            $fixture->register('persona@example.com', 'clave-filtrada-12');
            self::fail('La contraseña filtrada tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::PWNED, $exception->reason);
        }

        self::assertNull($fixture->accounts->findByEmail('persona@example.com'));
        self::assertSame([], RecordingAccountMailer::$messages);
    }

    public function testSiLaListaNoRespondeElAltaSeRechaza(): void
    {
        $fixture = $this->fixture();
        ConfigurablePwnedPasswordList::$fail = true;

        try {
            $fixture->register('persona@example.com', 'clave-local-segura');
            self::fail('El alta tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::UNAVAILABLE, $exception->reason);
        }

        self::assertNull($fixture->accounts->findByEmail('persona@example.com'));
    }

    public function testUnCorreoInvalidoNoCreaCuenta(): void
    {
        $fixture = $this->fixture();

        try {
            $fixture->register('no-es-correo', 'clave-local-segura');
            self::fail('El correo inválido tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::EMAIL, $exception->reason);
        }

        self::assertSame(0, ConfigurablePwnedPasswordList::$calls);
    }

    public function testElCorreoDuplicadoSeRechaza(): void
    {
        $fixture = $this->fixture();
        $fixture->register('persona@example.com', 'clave-local-segura');

        try {
            $fixture->register('persona@example.com', 'otra-clave-segura');
            self::fail('El correo duplicado tenía que rechazarse.');
        } catch (InvalidRegistration $exception) {
            self::assertSame(InvalidRegistration::DUPLICATE, $exception->reason);
        }
    }

    public function testElEnlaceCaducaALosTreintaMinutosYSoloSirveUnaVez(): void
    {
        $fixture = $this->fixture();
        $fixture->register('persona@example.com', 'clave-local-segura');
        $token = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $fixture->clock->modify('+30 minutes');

        try {
            $fixture->confirm($token);
            self::fail('El enlace caducado tenía que rechazarse.');
        } catch (InvalidConfirmation) {
            $account = $fixture->accounts->findByEmail('persona@example.com');
            self::assertNotNull($account);
            self::assertFalse($account->allowsFiscalAccess());
        }

        $fresh = $this->fixture();
        $fresh->register('persona@example.com', 'clave-local-segura');
        $freshToken = $this->tokenFrom(RecordingAccountMailer::$messages[0]['url']);
        $fresh->confirm($freshToken);

        $this->expectException(InvalidConfirmation::class);
        $fresh->confirm($freshToken);
    }

    private function tokenFrom(string $url): string
    {
        $token = basename($url);
        self::assertSame(64, strlen($token));

        return $token;
    }

    private function fixture(): RegistrationFixture
    {
        ConfigurablePwnedPasswordList::reset();
        RecordingAccountMailer::reset();
        $accounts = new InMemoryAccountRepository();
        $confirmations = new InMemoryEmailConfirmationRepository();
        $passwords = new ConfigurablePwnedPasswordList();
        $mailer = new RecordingAccountMailer();
        $clock = new MockClock('2026-10-25 16:00:00 UTC');
        $register = new RegisterAccount(
            $accounts,
            $confirmations,
            new InMemoryRegistrationStore(),
            new PrefixPasswordHasher(),
            $passwords,
            $mailer,
            new FixedConfirmationLink(),
            $clock,
        );
        $confirm = new ConfirmEmail($accounts, $confirmations, new InMemoryRegistrationStore(), $clock);

        return new RegistrationFixture($accounts, $passwords, $mailer, $clock, $register, $confirm);
    }
}
