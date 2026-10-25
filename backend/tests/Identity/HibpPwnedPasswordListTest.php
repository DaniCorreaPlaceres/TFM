<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\HibpPwnedPasswordList;
use App\Identity\PwnedPasswordLookupFailed;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class HibpPwnedPasswordListTest extends TestCase
{
    public function testUnaCoincidenciaConRecuentoPositivoEstaComprometida(): void
    {
        $hash = strtoupper(sha1('clave-local-segura'));
        $suffix = substr($hash, 5);
        $client = new MockHttpClient(function (string $method, string $url) use ($hash, $suffix): MockResponse {
            self::assertSame('GET', $method);
            self::assertSame('https://api.pwnedpasswords.com/range/'.substr($hash, 0, 5), $url);

            return new MockResponse("00000000000000000000000000000000000:0\r\n".$suffix.":4\r\n");
        });

        self::assertTrue((new HibpPwnedPasswordList($client))->contains('clave-local-segura'));
    }

    public function testUnRecuentoCeroNoEstaComprometido(): void
    {
        $suffix = substr(strtoupper(sha1('clave-local-segura')), 5);
        $client = new MockHttpClient([new MockResponse($suffix.':0')]);

        self::assertFalse((new HibpPwnedPasswordList($client))->contains('clave-local-segura'));
    }

    public function testUnFalloDeRedImpideDarLaContrasenaPorBuena(): void
    {
        $client = new MockHttpClient(function (): never {
            throw new TransportException('sin red');
        });

        $this->expectException(PwnedPasswordLookupFailed::class);
        (new HibpPwnedPasswordList($client))->contains('clave-local-segura');
    }
}
