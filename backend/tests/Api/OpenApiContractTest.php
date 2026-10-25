<?php

namespace App\Tests\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class OpenApiContractTest extends TestCase
{
    public function testElContratoMinimoDescribeSaludYErrores(): void
    {
        $document = Yaml::parseFile(dirname(__DIR__, 2).'/openapi/openapi.yaml');

        self::assertSame('3.0.3', $document['openapi']);
        self::assertSame(['/api/v1/health'], array_keys($document['paths']));
        $health = $document['paths']['/api/v1/health']['get'];
        self::assertSame(
            ['status'],
            $health['responses']['200']['content']['application/json']['schema']['required'],
        );
        self::assertArrayHasKey('X-Request-Id', $health['responses']['200']['headers']);
        self::assertArrayHasKey('RequestId', $document['components']['headers']);
        self::assertArrayHasKey('requestId', $document['components']['schemas']['Problem']['properties']);
        self::assertSame(
            '#/components/schemas/Problem',
            $health['responses']['default']['content']['application/problem+json']['schema']['$ref'],
        );
    }
}
