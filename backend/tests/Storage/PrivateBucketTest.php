<?php

namespace App\Tests\Storage;

use PHPUnit\Framework\TestCase;

final class PrivateBucketTest extends TestCase
{
    private const OBJECT = 'e0-private.txt';
    private const CONTENTS = 'e0-objeto-privado';

    public function testUnObjetoPrivadoNoSeDescargaSinCredenciales(): void
    {
        $endpoint = getenv('MINIO_ENDPOINT');
        if (!is_string($endpoint) || '' === $endpoint) {
            self::markTestSkipped('MINIO_ENDPOINT no está definido.');
        }

        $bucket = getenv('MINIO_BUCKET');
        $accessKey = getenv('MINIO_ACCESS_KEY');
        $secretKey = getenv('MINIO_SECRET_KEY');
        self::assertIsString($bucket);
        self::assertNotSame('', $bucket);
        self::assertIsString($accessKey);
        self::assertNotSame('', $accessKey);
        self::assertIsString($secretKey);
        self::assertNotSame('', $secretKey);

        $client = new S3Client($endpoint, $accessKey, $secretKey);
        $path = '/'.$bucket.'/'.self::OBJECT;

        $created = $client->send('PUT', '/'.$bucket);
        self::assertContains($created['status'], [200, 409]);

        $stored = $client->send('PUT', $path, self::CONTENTS);
        self::assertSame(200, $stored['status']);

        $authorized = $client->send('GET', $path);
        self::assertSame(200, $authorized['status']);
        self::assertStringContainsString(self::CONTENTS, $authorized['body']);

        $anonymous = $client->send('GET', $path, '', false);
        self::assertSame(403, $anonymous['status']);
        self::assertStringNotContainsString(self::CONTENTS, $anonymous['body']);
    }
}
