<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Identity\CloseOtherWebSessions;
use PHPUnit\Framework\TestCase;

final class CloseOtherWebSessionsTest extends TestCase
{
    public function testAvanzaElMarcadorSinCambiarLaContrasena(): void
    {
        $accounts = new InMemoryAccountRepository();
        $account = Account::register('persona@example.com', 'hashed:clave-local-segura', new \DateTimeImmutable('2026-11-01T21:40:00Z'));
        $accounts->save($account);
        $close = new CloseOtherWebSessions($accounts, new InMemoryRegistrationStore());

        $close($account);

        self::assertSame('1', $account->sessionMarker());
        self::assertSame('hashed:clave-local-segura', $accounts->findByEmail('persona@example.com')?->passwordHash());
    }
}
