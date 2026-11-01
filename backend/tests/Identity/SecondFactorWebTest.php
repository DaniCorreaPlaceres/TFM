<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AccountRepository;
use App\Identity\Base32;
use App\Identity\ProvisioningQr;
use App\Identity\Totp;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class SecondFactorWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testElSegundoFactorEsOpcionalYElCodigoDeRecuperacionSirveUnaVez(): void
    {
        $client = $this->client();
        $client->request('GET', '/entrar/codigo');
        self::assertResponseRedirects('/entrar');

        $email = 'segundo@example.com';
        $this->register($client, $email);
        $this->confirm($client, $email);
        $this->login($client, $email, 'clave-local-segura');
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();

        $client->clickLink('Segundo factor');
        $client->submit($client->getCrawler()->selectButton('Activar segundo factor')->form());
        self::assertResponseRedirects('/sesion/segundo-factor');
        $client->followRedirect();
        $uri = $client->getCrawler()->filter('#enlace')->attr('value');
        self::assertIsString($uri);
        self::assertSame(
            static::getContainer()->get(ProvisioningQr::class)->dataUri($uri),
            $client->getCrawler()->filter('img.qr')->attr('src'),
        );
        $encoded = $client->getCrawler()->filter('#secreto')->attr('value');
        self::assertIsString($encoded);
        $secret = Base32::decode($encoded);
        $code = static::getContainer()->get(Totp::class)->at($secret, intdiv(time(), 30));
        $client->submit($client->getCrawler()->selectButton('Confirmar segundo factor')->form([
            'code' => $code,
        ]));
        self::assertResponseRedirects('/sesion/segundo-factor/codigos');
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Códigos de recuperación');
        $codes = [];
        foreach ($client->getCrawler()->filter('ul.codigos li') as $item) {
            $codes[] = trim($item->textContent);
        }
        self::assertCount(8, $codes);
        $client->request('GET', '/sesion/segundo-factor/codigos');
        self::assertSelectorTextContains('main', 'ya se mostraron');

        $this->logout($client);
        $this->login($client, $email, 'clave-local-segura');
        self::assertResponseRedirects('/entrar/codigo');
        $client->followRedirect();
        $client->submit($client->getCrawler()->selectButton('Comprobar')->form([
            'code' => '000000',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'no es válido');

        $code = static::getContainer()->get(Totp::class)->at($secret, intdiv(time(), 30));
        $client->submit($client->getCrawler()->selectButton('Comprobar')->form([
            'code' => $code,
        ]));
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'El segundo factor está activo');

        $this->logout($client);
        $this->login($client, $email, 'clave-local-segura');
        $client->followRedirect();
        $client->submit($client->getCrawler()->selectButton('Comprobar')->form([
            'code' => $codes[0],
        ]));
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();

        $this->logout($client);
        $this->login($client, $email, 'clave-local-segura');
        $client->followRedirect();
        $client->submit($client->getCrawler()->selectButton('Comprobar')->form([
            'code' => $codes[0],
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'no es válido');

        $code = static::getContainer()->get(Totp::class)->at($secret, intdiv(time(), 30));
        $client->submit($client->getCrawler()->selectButton('Comprobar')->form([
            'code' => $code,
        ]));
        $client->followRedirect();
        $client->clickLink('Segundo factor');
        $client->submit($client->getCrawler()->selectButton('Desactivar segundo factor')->form([
            'code' => $codes[1],
        ]));
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();
        self::assertStringNotContainsString('El segundo factor está activo', $client->getCrawler()->filter('main')->text());

        $this->logout($client);
        $this->login($client, $email, 'clave-local-segura');
        self::assertResponseRedirects('/sesion');
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

    private function confirm(KernelBrowser $client, string $email): void
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
        $account = static::getContainer()->get(AccountRepository::class)->findByEmail($email);
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

    private function logout(KernelBrowser $client): void
    {
        $client->request('GET', '/sesion');
        $client->submit($client->getCrawler()->selectButton('Salir')->form());
        self::assertResponseRedirects('/entrar');
    }
}
