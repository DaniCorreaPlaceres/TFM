<?php

namespace App\Tests\Storage;

final class S3Client
{
    public function __construct(
        private readonly string $endpoint,
        private readonly string $accessKey,
        private readonly string $secretKey,
        private readonly string $region = 'us-east-1',
    ) {
    }

    /**
     * @return array{status: int, body: string}
     */
    public function send(string $method, string $path, string $body = '', bool $sign = true): array
    {
        $url = parse_url(rtrim($this->endpoint, '/').$path);
        if (!is_array($url) || !isset($url['host'])) {
            throw new \RuntimeException('El endpoint de MinIO no es válido.');
        }

        $host = $url['host'];
        $port = $url['port'] ?? 80;
        $hostHeader = $host.':'.$port;
        $amzDate = gmdate('Ymd\THis\Z');
        $date = gmdate('Ymd');
        $payloadHash = hash('sha256', $body);
        $signedHeaders = 'host;x-amz-content-sha256;x-amz-date';
        $canonicalHeaders = 'host:'.$hostHeader."\n".'x-amz-content-sha256:'.$payloadHash."\n".'x-amz-date:'.$amzDate."\n";
        $canonical = $method."\n".$path."\n\n".$canonicalHeaders."\n".$signedHeaders."\n".$payloadHash;
        $scope = $date.'/'.$this->region.'/s3/aws4_request';
        $stringToSign = "AWS4-HMAC-SHA256\n".$amzDate."\n".$scope."\n".hash('sha256', $canonical);
        $signingKey = hash_hmac(
            'sha256',
            'aws4_request',
            hash_hmac(
                'sha256',
                's3',
                hash_hmac(
                    'sha256',
                    $this->region,
                    hash_hmac('sha256', $date, 'AWS4'.$this->secretKey, true),
                    true,
                ),
                true,
            ),
            true,
        );
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $headers = $sign
            ? [
                'Host: '.$hostHeader,
                'x-amz-content-sha256: '.$payloadHash,
                'x-amz-date: '.$amzDate,
                'Authorization: AWS4-HMAC-SHA256 Credential='.$this->accessKey.'/'.$scope.', SignedHeaders='.$signedHeaders.', Signature='.$signature,
                'Content-Length: '.strlen($body),
                'Connection: close',
            ]
            : [
                'Host: '.$hostHeader,
                'Content-Length: '.strlen($body),
                'Connection: close',
            ];

        $request = $method.' '.$path." HTTP/1.1\r\n".implode("\r\n", $headers)."\r\n\r\n".$body;
        $socket = fsockopen($host, $port, $errno, $errstr, 5);
        if (false === $socket) {
            throw new \RuntimeException(sprintf('No se pudo conectar con MinIO: %s (%d).', $errstr, $errno));
        }

        stream_set_timeout($socket, 5);
        fwrite($socket, $request);
        $raw = stream_get_contents($socket);
        fclose($socket);
        if (false === $raw || !str_contains($raw, "\r\n\r\n")) {
            throw new \RuntimeException('MinIO no devolvió una respuesta HTTP completa.');
        }

        [$head, $responseBody] = explode("\r\n\r\n", $raw, 2);
        if (1 !== preg_match('#^HTTP/\d(?:\.\d)? (\d{3})#', $head, $matches)) {
            throw new \RuntimeException('MinIO devolvió una línea de estado ilegible.');
        }

        return ['status' => (int) $matches[1], 'body' => $responseBody];
    }
}
