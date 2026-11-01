<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FiscalEntityWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testLaCuentaCreaVariasEntidadesYEligeCualEstaActiva(): void
    {
        $client = $this->client();
        $client->request('GET', '/entidades');
        self::assertResponseRedirects('/entrar');

        $this->register($client, 'entidad@example.com');
        $this->confirm($client);
        $this->login($client, 'entidad@example.com');
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Todavía no has elegido una entidad fiscal');

        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => ' ',
            'tax_id' => '12345678Z',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'El nombre es obligatorio');

        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Norte',
            'tax_id' => '12345678z',
        ]));
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Entidad activa: Taller Norte');

        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Sur',
            'tax_id' => 'B12345678',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Entidad activa: Taller Sur');

        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Elegir')->form());
        self::assertResponseRedirects('/sesion');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Entidad activa: Taller Norte');

        $client->clickLink('Entidades');
        $token = $client->getCrawler()->selectButton('Elegir')->form()->get('_token')->getValue();
        $client->request('POST', '/entidades/00000000-0000-0000-0000-000000000000/elegir', [
            '_token' => $token,
        ]);
        self::assertResponseStatusCodeSame(404);
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
