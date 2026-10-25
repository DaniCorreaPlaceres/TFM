<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Identity\AccountRepository;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class RegistrationWebTest extends WebTestCase
{
    public static function setUpBeforeClass(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $output = new NullOutput();
        $create = $application->run(new ArrayInput([
            'command' => 'doctrine:database:create',
            '--if-not-exists' => true,
        ]), $output);
        $migrate = $application->run(new ArrayInput([
            'command' => 'doctrine:migrations:migrate',
            '--no-interaction' => true,
        ]), $output);
        $kernel->shutdown();
        if (0 !== $create || 0 !== $migrate) {
            throw new \RuntimeException('No se pudo preparar la base de prueba.');
        }
    }

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
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement('DELETE FROM email_confirmation');
        $connection->executeStatement('DELETE FROM account');

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
