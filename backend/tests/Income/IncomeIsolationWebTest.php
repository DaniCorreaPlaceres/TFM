<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Tests\Identity\ConfigurablePwnedPasswordList;
use App\Tests\Identity\PreparesAccountSchema;
use App\Tests\Identity\RecordingAccountMailer;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\FileFormField;

final class IncomeIsolationWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testOtraCuentaYLaEmpleadaNoVenElIngreso(): void
    {
        $client = $this->client();
        $this->register($client, 'titular-ingreso@example.com');
        $this->confirm($client);
        $this->login($client, 'titular-ingreso@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Norte',
            'tax_id' => 'B12345674',
        ]));
        $client->followRedirect();
        $client->clickLink('Contactos');
        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Ana Ruiz',
            'nif' => '12345678Z',
            'cliente' => '1',
        ]);
        $client->followRedirect();
        $client->clickLink('Ingresos y gastos');
        $contact = $client->getCrawler()->filter('#contacto option')->attr('value');
        self::assertIsString($contact);
        $client->request('POST', '/ingresos', [
            '_token' => $this->token($client),
            'contacto' => $contact,
            'numero' => 'F-9',
            'fecha' => '2026-11-02',
            'descripcion' => ['Consultoría'],
            'cantidad' => ['1'],
            'precio' => ['10'],
            'iva' => ['21'],
            'retencion' => ['0'],
        ]);
        $client->followRedirect();
        $income = $client->getRequest()->getPathInfo();
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        self::assertIsString($png);
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aselto-income-isolation';
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        $path = $directory.DIRECTORY_SEPARATOR.'nota.png';
        file_put_contents($path, $png);
        $form = $client->getCrawler()->selectButton('Adjuntar')->form();
        $field = $form['archivo'];
        self::assertInstanceOf(FileFormField::class, $field);
        $field->upload($path);
        $client->submit($form);
        $client->followRedirect();
        $download = $client->getCrawler()->selectLink('nota.png')->attr('href');
        self::assertIsString($download);

        $client->request('GET', '/entidades');
        $client->submit($client->getCrawler()->selectButton('Invitar asesoría')->form([
            'advisory_email' => 'admin-ingreso@example.com',
        ]));
        $invitation = $this->latestInvitationPath();
        $this->logout($client);

        $this->register($client, 'admin-ingreso@example.com');
        $this->confirm($client);
        $this->login($client, 'admin-ingreso@example.com');
        $client->request('GET', '/asesorias');
        $client->submit($client->getCrawler()->selectButton('Crear asesoría')->form([
            'name' => 'Norte Asesores',
            'tax_id' => 'B0000002B',
        ]));
        $client->followRedirect();
        $firmId = $this->firmId('Norte Asesores');
        $client->request('GET', $invitation);
        $target = $client->getCrawler()->filter('input[name="target"]')->attr('value');
        self::assertIsString($target);
        $client->submit($client->getCrawler()->selectButton('Aceptar vínculo')->form([
            'target' => $target,
        ]));
        $client->followRedirect();
        $client->clickLink('Ver ingresos');
        self::assertSelectorTextContains('main', 'Taller Norte');
        self::assertSelectorTextContains('main', 'F-9');
        self::assertStringNotContainsString('Registrar cobro', (string) $client->getResponse()->getContent());
        $client->clickLink('F-9');
        $advisoryDownload = $client->getCrawler()->selectLink('nota.png')->attr('href');
        self::assertIsString($advisoryDownload);
        $client->request('GET', $advisoryDownload);
        self::assertSame($png, $client->getResponse()->getContent());
        $client->request('POST', $income.'/cobros', [
            '_token' => 'caducado',
            'version' => '1',
            'payment_token' => '11111111-1111-1111-1111-111111111111',
            'importe' => '1',
            'fecha_cobro' => '2026-11-02',
            'metodo' => 'efectivo',
        ]);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Ana Ruiz', (string) $client->getResponse()->getContent());

        $client->request('GET', '/asesorias/'.$firmId);
        $client->submit($client->getCrawler()->filter('form[action$="/invitar"]')->form([
            'email' => 'empleada-ingreso@example.com',
        ]));
        $this->logout($client);
        $this->register($client, 'empleada-ingreso@example.com');
        $this->confirm($client);
        $this->login($client, 'empleada-ingreso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertStringNotContainsString('Taller Norte', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Ver ingresos', (string) $client->getResponse()->getContent());
        $client->request('GET', '/asesorias/'.$firmId.'/entidades/'.$this->entityId('Taller Norte').'/ingresos');
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Taller Norte', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Ana Ruiz', (string) $client->getResponse()->getContent());

        $this->logout($client);
        $this->register($client, 'ajena-ingreso@example.com');
        $this->confirm($client);
        $this->login($client, 'ajena-ingreso@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Sur',
            'tax_id' => 'B0000003B',
        ]));
        $client->followRedirect();
        $client->request('GET', '/ingresos');
        self::assertStringNotContainsString('Ana Ruiz', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('F-9', (string) $client->getResponse()->getContent());
        $client->request('GET', $income);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Ana Ruiz', (string) $client->getResponse()->getContent());
        $client->request('GET', $download);
        self::assertResponseStatusCodeSame(404);
        self::assertNotSame($png, $client->getResponse()->getContent());

        $session = $client->getRequest()->getSession();
        $session->set('fiscal_entity_id', $this->entityId('Taller Norte'));
        $session->save();
        $client->request('GET', '/contactos');
        $poisoned = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Ana Ruiz', $poisoned);
        self::assertStringNotContainsString('12345678Z', $poisoned);
    }

    private function client(): KernelBrowser
    {
        ConfigurablePwnedPasswordList::reset();
        RecordingAccountMailer::reset();
        MemoryObjectStore::reset();
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

    private function token(KernelBrowser $client): string
    {
        $token = $client->getCrawler()->filter('input[name="_token"]')->attr('value');
        self::assertIsString($token);

        return $token;
    }

    private function latestInvitationPath(): string
    {
        $invitation = null;
        foreach (RecordingAccountMailer::$messages as $message) {
            if ('advisory-client-invitation' === $message['kind']) {
                $invitation = $message;
            }
        }
        self::assertNotNull($invitation);
        $path = parse_url($invitation['url'], PHP_URL_PATH);
        self::assertIsString($path);

        return $path;
    }

    private function firmId(string $name): string
    {
        $id = static::getContainer()->get(Connection::class)->fetchOne('SELECT id FROM advisory_firm WHERE name = ?', [$name]);
        self::assertIsString($id);

        return $id;
    }

    private function entityId(string $name): string
    {
        $id = static::getContainer()->get(Connection::class)->fetchOne('SELECT id FROM fiscal_entity WHERE legal_name = ?', [$name]);
        self::assertIsString($id);

        return $id;
    }
}
