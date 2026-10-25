<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AccountRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class SessionWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testUnaCuentaSinConfirmarNoEntra(): void
    {
        $client = $this->client();
        $this->register($client, 'persona@example.com');
        $this->login($client, 'persona@example.com', 'clave-local-segura');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Confirma el correo');
        $client->request('GET', '/sesion');
        self::assertResponseRedirects('/entrar');
    }

    public function testElCorreoDesconocidoYLaContrasenaIncorrectaDicenLoMismo(): void
    {
        $client = $this->client();
        $this->register($client, 'persona@example.com');
        $this->confirm($client);

        $this->login($client, 'nadie@example.com', 'clave-local-segura');
        self::assertResponseStatusCodeSame(422);
        $unknown = $client->getCrawler()->filter('.error')->text();
        self::assertSame('nadie@example.com', $client->getCrawler()->filter('#email')->attr('value'));

        $this->login($client, 'persona@example.com', 'clave-equivocada');
        self::assertResponseStatusCodeSame(422);
        self::assertSame($unknown, $client->getCrawler()->filter('.error')->text());
    }

    public function testUnaCuentaConfirmadaEntraYSale(): void
    {
        $client = $this->client();
        $this->register($client, 'persona@example.com');
        $this->confirm($client);
        $this->login($client, 'persona@example.com', 'clave-local-segura');

        self::assertResponseRedirects('/sesion');
        $setCookie = $client->getResponse()->headers->all('set-cookie');
        self::assertCount(1, $setCookie);
        $header = strtolower($setCookie[0]);
        self::assertStringContainsString('max-age=7200', $header);
        self::assertStringContainsString('httponly', $header);
        self::assertStringContainsString('samesite=lax', $header);
        self::assertStringNotContainsString('secure', $header);
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Has entrado');
        self::assertSelectorTextContains('main', 'persona@example.com');

        $client->submit($client->getCrawler()->selectButton('Salir')->form());
        self::assertResponseRedirects('/entrar');
        $client->request('GET', '/sesion');
        self::assertResponseRedirects('/entrar');
    }

    public function testLaRecuperacionNoRevelaElCorreoYCierraLaSesionAnterior(): void
    {
        $client = $this->client();
        $this->askReset($client, 'nadie@example.com');
        $unknown = $client->getCrawler()->filter('h1')->text();
        self::assertSame([], array_values(array_filter(
            RecordingAccountMailer::$messages,
            static fn (array $message): bool => 'reset' === $message['kind'],
        )));

        $this->register($client, 'persona@example.com');
        $this->askReset($client, 'persona@example.com');
        self::assertSame($unknown, $client->getCrawler()->filter('h1')->text());
        self::assertSame([], array_values(array_filter(
            RecordingAccountMailer::$messages,
            static fn (array $message): bool => 'reset' === $message['kind'],
        )));

        $this->confirm($client);
        $this->login($client, 'persona@example.com', 'clave-local-segura');
        $client->followRedirect();
        $this->askReset($client, 'persona@example.com');
        self::assertSame($unknown, $client->getCrawler()->filter('h1')->text());
        $reset = null;
        foreach (RecordingAccountMailer::$messages as $message) {
            if ('reset' === $message['kind']) {
                $reset = $message;
            }
        }
        self::assertNotNull($reset);
        $path = parse_url($reset['url'], PHP_URL_PATH);
        self::assertIsString($path);

        $client->request('GET', $path);
        $client->submit($client->getCrawler()->selectButton('Guardar contraseña')->form([
            'password' => 'clave-local-nueva1',
        ]));
        self::assertResponseRedirects('/entrar?actualizada=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Contraseña actualizada');

        $client->request('GET', '/sesion');
        self::assertResponseRedirects('/entrar');

        $this->login($client, 'persona@example.com', 'clave-local-segura');
        self::assertResponseStatusCodeSame(422);
        $this->login($client, 'persona@example.com', 'clave-local-nueva1');
        self::assertResponseRedirects('/sesion');
    }

    public function testLaCookieDeSesionDuraDosHorasYElAccesoSeBloqueaAlSextoIntento(): void
    {
        $client = $this->client();
        /** @var array<string, mixed> $options */
        $options = static::getContainer()->getParameter('session.storage.options');
        self::assertSame(7200, $options['cookie_lifetime']);
        self::assertSame(7200, $options['gc_maxlifetime']);
        self::assertTrue($options['cookie_httponly']);
        self::assertSame('lax', $options['cookie_samesite']);
        self::assertSame('aselto_sesion', $options['name']);

        $limiter = static::getContainer()->get('limiter.login_attempt');
        self::assertInstanceOf(RateLimiterFactory::class, $limiter);
        $key = 'bloqueo-config-'.bin2hex(random_bytes(4));
        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            self::assertTrue($limiter->create($key)->consume(1)->isAccepted());
        }
        self::assertFalse($limiter->create($key)->consume(1)->isAccepted());

        $email = 'bloqueo-'.bin2hex(random_bytes(4)).'@example.com';
        for ($attempt = 1; $attempt <= 5; ++$attempt) {
            $this->login($client, $email, 'clave-equivocada');
            self::assertSelectorTextContains('.error', 'no son correctos');
        }
        $this->login($client, $email, 'clave-equivocada');
        self::assertSelectorTextContains('.error', 'Demasiados intentos');
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
        self::assertResponseIsSuccessful();
        $account = static::getContainer()->get(AccountRepository::class)->findByEmail('persona@example.com');
        self::assertNotNull($account);
        self::assertTrue($account->allowsFiscalAccess());
    }

    private function login(KernelBrowser $client, string $email, string $password): void
    {
        $crawler = $client->request('GET', '/entrar');
        $client->submit($crawler->selectButton('Entrar')->form([
            'email' => $email,
            'password' => $password,
        ]));
    }

    private function askReset(KernelBrowser $client, string $email): void
    {
        $crawler = $client->request('GET', '/recuperar');
        $client->submit($crawler->selectButton('Enviar enlace')->form([
            'email' => $email,
        ]));
        self::assertResponseRedirects('/recuperar/enviado');
        $client->followRedirect();
    }
}
