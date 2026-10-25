<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AccountRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegistrationWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testElAltaWebConfirmaElCorreoSinBuzonReal(): void
    {
        $client = $this->client();
        $this->submit($client, 'persona@example.com', 'clave-local-segura');
        self::assertResponseRedirects('/registro/enviado');
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Revisa tu correo');

        $account = static::getContainer()->get(AccountRepository::class)->findByEmail('persona@example.com');
        self::assertNotNull($account);
        self::assertFalse($account->allowsFiscalAccess());

        self::assertCount(1, RecordingAccountMailer::$messages);
        $path = parse_url(RecordingAccountMailer::$messages[0]['url'], PHP_URL_PATH);
        self::assertIsString($path);

        $client->request('GET', $path);
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Correo confirmado');
        self::assertTrue(static::getContainer()->get(AccountRepository::class)->findByEmail('persona@example.com')->allowsFiscalAccess());

        $client->request('GET', $path);
        self::assertResponseStatusCodeSame(400);
        self::assertSelectorTextContains('h1', 'Enlace no válido');
    }

    public function testElFormularioConservaElCorreoSiLaContrasenaEsCorta(): void
    {
        $client = $this->client();
        $this->submit($client, 'persona@example.com', 'corta');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'al menos 12 caracteres');
        self::assertSame('persona@example.com', $client->getCrawler()->filter('#email')->attr('value'));
        self::assertNull(static::getContainer()->get(AccountRepository::class)->findByEmail('persona@example.com'));
    }

    public function testElFormularioRechazaUnaContrasenaFiltrada(): void
    {
        $client = $this->client();
        ConfigurablePwnedPasswordList::$compromised[] = 'clave-filtrada-12';
        $this->submit($client, 'persona@example.com', 'clave-filtrada-12');

        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'filtraciones conocidas');
        self::assertNull(static::getContainer()->get(AccountRepository::class)->findByEmail('persona@example.com'));
    }

    private function client(): KernelBrowser
    {
        ConfigurablePwnedPasswordList::reset();
        RecordingAccountMailer::reset();
        $client = static::createClient();
        self::emptyAccounts();

        return $client;
    }

    private function submit(KernelBrowser $client, string $email, string $password): void
    {
        $crawler = $client->request('GET', '/registro');
        $client->submit($crawler->selectButton('Crear cuenta')->form([
            'email' => $email,
            'password' => $password,
        ]));
    }
}
