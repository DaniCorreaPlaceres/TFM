<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HealthTest extends WebTestCase
{
    public function testLaSaludRespondeOkConRequestId(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/health');

        $response = $client->getResponse();
        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringStartsWith('application/json', (string) $response->headers->get('Content-Type'));
        self::assertSame(['status' => 'ok'], $payload);
        $requestId = (string) $response->headers->get('X-Request-Id');
        self::assertMatchesRegularExpression(self::uuid(), $requestId);
        self::assertStringContainsString($requestId, (string) file_get_contents(static::getContainer()->getParameter('kernel.logs_dir').'/test.log'));
    }

    public function testLaBienvenidaNoUsaProblemJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $response = $client->getResponse();

        self::assertSame(404, $response->getStatusCode());
        self::assertStringNotContainsString('application/problem+json', (string) $response->headers->get('Content-Type'));
    }

    private static function uuid(): string
    {
        return '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
    }
}
