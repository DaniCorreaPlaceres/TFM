<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

final class CloseOtherSessionsWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testCerrarLasDemasDejaLaSesionActualYExpulsaLaOtra(): void
    {
        $first = $this->client();
        $first->request('POST', '/sesion/cerrar-demas');
        self::assertResponseRedirects('/entrar');

        $this->register($first, 'sesiones@example.com');
        $this->confirm($first);
        $this->login($first, 'sesiones@example.com');
        $first->followRedirect();
        $current = $this->cookies($first);

        $this->useCookies($first, []);
        $this->login($first, 'sesiones@example.com');
        $first->followRedirect();
        self::assertSelectorTextContains('h1', 'Has entrado');
        $other = $this->cookies($first);

        $this->useCookies($first, $current);
        $first->request('GET', '/sesion');
        $first->submit($first->getCrawler()->selectButton('Cerrar las demás sesiones')->form());
        self::assertResponseRedirects('/sesion?cerradas=1');
        $first->followRedirect();
        self::assertSelectorTextContains('h1', 'Has entrado');
        self::assertSelectorTextContains('main', 'Las demás sesiones se han cerrado');
        $current = $this->cookies($first);

        $this->useCookies($first, $other);
        $first->request('GET', '/sesion');
        self::assertResponseRedirects('/entrar');

        $this->login($first, 'sesiones@example.com');
        $first->followRedirect();
        $other = $this->cookies($first);
        $this->useCookies($first, $current);
        $first->request('POST', '/sesion/cerrar-demas', ['_token' => 'caducado']);
        self::assertResponseRedirects('/sesion');
        $this->useCookies($first, $other);
        $first->request('GET', '/sesion');
        self::assertResponseIsSuccessful();
        $this->useCookies($first, $current);
        $first->request('GET', '/sesion');
        self::assertResponseIsSuccessful();
    }

    /**
     * @return list<Cookie>
     */
    private function cookies(KernelBrowser $client): array
    {
        return array_values(array_map(
            static fn (Cookie $cookie): Cookie => clone $cookie,
            $client->getCookieJar()->all(),
        ));
    }

    /**
     * @param list<Cookie> $cookies
     */
    private function useCookies(KernelBrowser $client, array $cookies): void
    {
        $jar = $client->getCookieJar();
        $jar->clear();
        foreach ($cookies as $cookie) {
            $jar->set($cookie);
        }
    }

    private function client(): KernelBrowser
    {
        ConfigurablePwnedPasswordList::reset();
        RecordingAccountMailer::reset();
        $client = static::createClient();
        static::getContainer()->get('cache.rate_limiter')->clear();
        self::emptyAccounts();

        return $client;
    }

    private function register(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request('GET', '/registro');
        $client->submit($crawler->selectButton('Crear cuenta')->form([
            'email' => $email,
            'password' => 'clave-local-segura',
        ]));
        self::assertResponseRedirects('/registro/enviado');
    }

    private function confirm(KernelBrowser $client): void
    {
        $confirmation = null;
        foreach (RecordingAccountMailer::$messages as $message) {
            if ('confirmation' === $message['kind']) {
                $confirmation = $message;
            }
        }
        self::assertNotNull($confirmation);
        $path = parse_url($confirmation['url'], PHP_URL_PATH);
        self::assertIsString($path);
        $client->request('GET', $path);
    }

    private function login(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request('GET', '/entrar');
        $client->submit($crawler->selectButton('Entrar')->form([
            'email' => $email,
            'password' => 'clave-local-segura',
        ]));
    }
}
