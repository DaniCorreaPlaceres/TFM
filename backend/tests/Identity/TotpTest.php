<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\Totp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class TotpTest extends TestCase
{
    public function testElVectorRfcEnElSegundo59(): void
    {
        $totp = new Totp(new MockClock('@59'));

        self::assertSame('287082', $totp->at('12345678901234567890', 1));
        self::assertTrue($totp->verify('12345678901234567890', '287082'));
    }

    public function testElVectorRfcEnElInstante1111111111(): void
    {
        $totp = new Totp(new MockClock('@1111111111'));

        self::assertSame('050471', $totp->at('12345678901234567890', 37037037));
        self::assertTrue($totp->verify('12345678901234567890', '050471'));
    }

    public function testAceptaElIntervaloAnteriorYRechazaElQueQuedaDosPasosAtras(): void
    {
        $clock = new MockClock('@1111111111');
        $totp = new Totp($clock);
        $clock->sleep(30);
        self::assertTrue($totp->verify('12345678901234567890', '050471'));

        $clock->sleep(60);
        self::assertFalse($totp->verify('12345678901234567890', '050471'));
        self::assertFalse($totp->verify('12345678901234567890', '12345'));
    }
}
