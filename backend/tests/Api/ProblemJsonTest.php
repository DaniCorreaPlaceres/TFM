<?php

namespace App\Tests\Api;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProblemJsonTest extends WebTestCase
{
    public function testUnaRutaDesconocidaDevuelveProblemJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/no-existe');

        $this->assertProblem($client->getResponse(), 404, 'No encontrado');
    }

    public function testUnMetodoNoPermitidoDevuelveProblemJson(): void
    {
        $client = static::createClient();
        $client->request('POST', '/api/v1/health');

        $this->assertProblem($client->getResponse(), 405, 'Método no permitido');
    }

    private function assertProblem(\Symfony\Component\HttpFoundation\Response $response, int $status, string $title): void
    {
        $payload = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $requestId = (string) $response->headers->get('X-Request-Id');

        self::assertSame($status, $response->getStatusCode());
        self::assertStringStartsWith('application/problem+json', (string) $response->headers->get('Content-Type'));
        self::assertSame('about:blank', $payload['type']);
        self::assertSame($title, $payload['title']);
        self::assertSame($status, $payload['status']);
        self::assertSame($requestId, $payload['requestId']);
        self::assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $requestId);
        self::assertArrayNotHasKey('trace', $payload);
    }
}
