<?php

declare(strict_types=1);

namespace App\Identity;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HibpPwnedPasswordList implements PwnedPasswordList
{
    public function __construct(private readonly HttpClientInterface $http)
    {
    }

    public function contains(string $password): bool
    {
        $hash = strtoupper(sha1($password));
        $prefix = substr($hash, 0, 5);
        $suffix = substr($hash, 5);

        try {
            $response = $this->http->request('GET', 'https://api.pwnedpasswords.com/range/'.$prefix, [
                'headers' => [
                    'Add-Padding' => 'true',
                    'User-Agent' => 'Aselto-TFM',
                ],
            ]);
            $status = $response->getStatusCode();
            $body = $response->getContent(false);
        } catch (TransportExceptionInterface) {
            throw new PwnedPasswordLookupFailed();
        }

        if (200 !== $status) {
            throw new PwnedPasswordLookupFailed();
        }

        $lines = preg_split("/\r\n|\n|\r/", $body);
        if (false === $lines) {
            throw new PwnedPasswordLookupFailed();
        }

        foreach ($lines as $line) {
            if (!str_contains($line, ':')) {
                continue;
            }

            [$candidate, $count] = explode(':', $line, 2);
            $candidate = strtoupper(trim($candidate));
            if (strlen($candidate) === strlen($suffix) && hash_equals($suffix, $candidate) && (int) trim($count) > 0) {
                return true;
            }
        }

        return false;
    }
}
