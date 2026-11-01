<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\ProvisioningQr;
use PHPUnit\Framework\TestCase;

final class ProvisioningQrTest extends TestCase
{
    public function testElQrEsSvgDelEnlaceYCambiaSiCambiaElEnlace(): void
    {
        $qr = new ProvisioningQr();
        $uri = 'otpauth://totp/Aselto:persona%40example.com?secret=GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ&issuer=Aselto&algorithm=SHA1&digits=6&period=30';
        $image = $qr->dataUri($uri);

        self::assertStringStartsWith('data:image/svg+xml;base64,', $image);
        $svg = base64_decode(substr($image, strlen('data:image/svg+xml;base64,')), true);
        self::assertIsString($svg);
        self::assertStringContainsString('<svg ', $svg);
        self::assertStringContainsString('fill="#000"', $svg);
        self::assertStringContainsString('fill="#fff"', $svg);
        self::assertStringNotContainsString($uri, $svg);
        self::assertNotSame($image, $qr->dataUri($uri.'&extra=1'));
    }
}
