<?php

declare(strict_types=1);

namespace App\Income;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class S3ObjectStore implements ObjectStore
{
    public function __construct(
        private readonly HttpClientInterface $http,
        private readonly string $endpoint,
        private readonly string $bucket,
        private readonly string $accessKey,
        private readonly string $secretKey,
    ) {
    }

    public function put(string $key, string $body): void
    {
        $status = $this->request('PUT', $key, $body);
        if (200 !== $status && 204 !== $status) {
            throw new \RuntimeException('No se ha podido guardar el archivo.');
        }
    }

    public function get(string $key): string
    {
        [$status, $body] = $this->exchange('GET', $key, '');
        if (200 !== $status) {
            throw new \RuntimeException('No se ha podido leer el archivo.');
        }

        return $body;
    }

    private function request(string $method, string $key, string $body): int
    {
        [$status] = $this->exchange($method, $key, $body);

        return $status;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private function exchange(string $method, string $key, string $body): array
    {
        $url = parse_url($this->endpoint);
        if (!is_array($url) || !isset($url['host'])) {
            throw new \RuntimeException('El almacén no está configurado.');
        }
        $scheme = $url['scheme'] ?? 'http';
        $port = $url['port'] ?? ('https' === $scheme ? 443 : 80);
        $hostHeader = (80 === $port && 'http' === $scheme) || (443 === $port && 'https' === $scheme)
            ? $url['host']
            : $url['host'].':'.$port;
        $path = '/'.$this->bucket.'/'.implode('/', array_map(rawurlencode(...), explode('/', $key)));
        $amzDate = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');
        $payload = hash('sha256', $body);
        $canonicalHeaders = 'host:'.$hostHeader."\n".'x-amz-content-sha256:'.$payload."\n".'x-amz-date:'.$amzDate."\n";
        $signed = 'host;x-amz-content-sha256;x-amz-date';
        $canonical = $method."\n".$path."\n\n".$canonicalHeaders."\n".$signed."\n".$payload;
        $scope = $date.'/us-east-1/s3/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n".$amzDate."\n".$scope."\n".hash('sha256', $canonical);
        $signingKey = hash_hmac(
            'sha256',
            'aws4_request',
            hash_hmac(
                'sha256',
                's3',
                hash_hmac(
                    'sha256',
                    'us-east-1',
                    hash_hmac('sha256', $date, 'AWS4'.$this->secretKey, true),
                    true,
                ),
                true,
            ),
            true,
        );
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);
        $response = $this->http->request($method, $scheme.'://'.$hostHeader.$path, [
            'headers' => [
                'Host' => $hostHeader,
                'x-amz-content-sha256' => $payload,
                'x-amz-date' => $amzDate,
                'Authorization' => 'AWS4-HMAC-SHA256 Credential='.$this->accessKey.'/'.$scope.', SignedHeaders='.$signed.', Signature='.$signature,
            ],
            'body' => $body,
        ]);

        return [$response->getStatusCode(), $response->getContent(false)];
    }
}
