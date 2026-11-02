<?php

declare(strict_types=1);

namespace App\Tests\Web;

use App\Tests\Identity\ConfigurablePwnedPasswordList;
use App\Tests\Identity\PreparesAccountSchema;
use App\Tests\Identity\RecordingAccountMailer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LandingWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testLaPortadaPresentaAutonomosYAsesorias(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertStringStartsWith('text/html', (string) $client->getResponse()->headers->get('Content-Type'));
        self::assertSelectorTextContains('h1', 'El autónomo y su asesoría');
        self::assertSelectorTextContains('main', 'Tus finanzas bajo control');
        self::assertSelectorTextContains('#autonomos', 'Para autónomos');
        self::assertSelectorTextContains('#asesorias', 'Para asesorías');
        self::assertSelectorTextContains('main', 'modelos 130, 303 y 420');
        self::assertSelectorTextContains('main', 'Desde el primer día');
        self::assertSelectorTextContains('main', 'En las siguientes entregas');
        self::assertSelectorNotExists('nav[aria-label="Secciones"]');

        $html = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Todo claro', $html);
        self::assertStringNotContainsString('Todo al día', $html);
        self::assertStringNotContainsString('1.250', $html);
        self::assertStringNotContainsString('€', $html);

        $crawler = $client->getCrawler();
        self::assertSame('/registro', $crawler->selectLink('Crear cuenta')->first()->attr('href'));
        self::assertSame('/entrar', $crawler->selectLink('Entrar')->first()->attr('href'));

        $client->request('GET', '/entrar');
        self::assertSelectorTextContains('h1', 'Entrar');
        self::assertSame('/', $client->getCrawler()->filter('.marca-puerta')->attr('href'));
    }

    public function testQuienHaEntradoSigueViendoLaPortadaYSuEspacio(): void
    {
        $client = $this->client();
        $this->register($client, 'persona@example.com');
        $this->confirm($client);
        $this->login($client, 'persona@example.com', 'clave-local-segura');
        self::assertResponseRedirects('/sesion');

        $client->request('GET', '/');
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'El autónomo y su asesoría');
        self::assertSelectorNotExists('nav[aria-label="Secciones"]');
        self::assertSame('/sesion', $client->getCrawler()->selectLink('Ir a mi espacio')->first()->attr('href'));
        self::assertSelectorNotExists('a[href="/registro"]');

        $client->request('GET', '/sesion');
        self::assertSelectorTextContains('h1', 'Inicio');
        self::assertSelectorTextContains('nav', 'Ingresos y gastos');
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
    }

    private function login(KernelBrowser $client, string $email, string $password): void
    {
        $crawler = $client->request('GET', '/entrar');
        $client->submit($crawler->selectButton('Entrar')->form([
            'email' => $email,
            'password' => $password,
        ]));
    }
}
