<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FiscalEntityIsolationWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testOtraCuentaNoLeeNiCambiaLaEntidad(): void
    {
        $client = $this->client();
        $this->register($client, 'titular@example.com');
        $this->confirm($client);
        $this->login($client, 'titular@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Ajeno',
            'tax_id' => 'A0000001A',
        ]));
        $client->followRedirect();
        $foreignId = $this->entityId('Taller Ajeno');

        $this->logout($client);

        $this->register($client, 'ajena@example.com');
        $this->confirm($client);
        $this->login($client, 'ajena@example.com');
        $client->followRedirect();
        self::assertStringNotContainsString('Taller Ajeno', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('A0000001A', (string) $client->getResponse()->getContent());

        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Propio',
            'tax_id' => 'B0000001B',
        ]));
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Este',
            'tax_id' => 'E0000001E',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Entidad activa: Taller Este');

        $client->clickLink('Entidades');
        $list = (string) $client->getResponse()->getContent();
        self::assertStringContainsString('Taller Propio', $list);
        self::assertStringContainsString('Taller Este', $list);
        self::assertStringNotContainsString('Taller Ajeno', $list);
        self::assertStringNotContainsString('A0000001A', $list);

        $token = $client->getCrawler()->selectButton('Elegir')->form()->get('_token')->getValue();
        $client->request('POST', '/entidades/'.$foreignId.'/elegir', [
            '_token' => $token,
        ]);
        self::assertResponseStatusCodeSame(404);
        $denied = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Taller Ajeno', $denied);
        self::assertStringNotContainsString('A0000001A', $denied);
        self::assertSame('Taller Ajeno', $this->storedName($foreignId));
        self::assertSame('A0000001A', $this->storedTaxId($foreignId));

        $client->request('GET', '/sesion');
        self::assertSelectorTextContains('main', 'Entidad activa: Taller Este');
        self::assertStringNotContainsString('Taller Ajeno', (string) $client->getResponse()->getContent());

        $session = $client->getRequest()->getSession();
        $session->set('fiscal_entity_id', $foreignId);
        $session->save();
        $client->request('GET', '/sesion');
        $poisoned = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Taller Ajeno', $poisoned);
        self::assertStringNotContainsString('A0000001A', $poisoned);
        self::assertStringContainsString('Todavía no has elegido una entidad fiscal', $poisoned);
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

    private function logout(KernelBrowser $client): void
    {
        $client->request('GET', '/sesion');
        $client->submit($client->getCrawler()->selectButton('Salir')->form());
        self::assertResponseRedirects('/entrar');
    }

    private function entityId(string $legalName): string
    {
        $id = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT id FROM fiscal_entity WHERE legal_name = ?',
            [$legalName],
        );
        self::assertIsString($id);

        return $id;
    }

    private function storedName(string $id): string
    {
        $name = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT legal_name FROM fiscal_entity WHERE id = ?',
            [$id],
        );
        self::assertIsString($name);

        return $name;
    }

    private function storedTaxId(string $id): string
    {
        $taxId = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT tax_id FROM fiscal_entity WHERE id = ?',
            [$id],
        );
        self::assertIsString($taxId);

        return $taxId;
    }
}
