<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdvisoryWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testLaAdministradoraInvitaYLaEmpleadaEntraSinSegundoFactor(): void
    {
        $client = $this->client();
        $client->request('GET', '/asesorias');
        self::assertResponseRedirects('/entrar');

        $this->register($client, 'admin-asesoria@example.com');
        $this->confirm($client);
        $this->login($client, 'admin-asesoria@example.com');
        $client->followRedirect();
        $client->clickLink('Asesorías');
        $client->request('POST', '/asesorias', [
            '_token' => 'caducado',
            'name' => 'Norte Asesores',
            'tax_id' => 'B0000001B',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'El formulario ha caducado');

        $client->submit($client->getCrawler()->selectButton('Crear asesoría')->form([
            'name' => ' ',
            'tax_id' => 'B0000001B',
        ]));
        self::assertResponseStatusCodeSame(422);

        $client->submit($client->getCrawler()->selectButton('Crear asesoría')->form([
            'name' => 'Norte Asesores',
            'tax_id' => 'b-0000001b',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Norte Asesores');
        self::assertSelectorTextContains('main', 'B0000001B');
        $firmId = $this->firmId('Norte Asesores');

        $client->submit($client->getCrawler()->selectButton('Invitar')->form([
            'email' => 'empleada-asesoria@example.com',
        ]));
        self::assertResponseRedirects('/asesorias/'.$firmId.'?invitada=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Hemos enviado la invitación');
        $sent = count(RecordingAccountMailer::$messages);
        $client->request('POST', '/asesorias/'.$firmId.'/invitar', [
            '_token' => 'caducado',
            'email' => 'otra@example.com',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'El formulario ha caducado');
        self::assertCount($sent, RecordingAccountMailer::$messages);
        $invitation = $this->latestInvitationPath();

        $client->request('GET', $invitation);
        self::assertSelectorTextContains('main', 'Esta invitación es para otro correo');

        $this->logout($client);
        $this->register($client, 'empleada-asesoria@example.com');
        $this->confirm($client);
        $this->login($client, 'empleada-asesoria@example.com');
        $client->followRedirect();
        self::assertStringNotContainsString('El segundo factor está activo', (string) $client->getResponse()->getContent());

        $client->request('GET', '/asesorias');
        self::assertSelectorTextContains('main', 'Norte Asesores');
        self::assertSelectorTextContains('main', 'empleado');
        self::assertStringNotContainsString('Crear asesoría', (string) $client->getResponse()->getContent());
        $request = $client->getRequest();
        $client->getContainer()->get('request_stack')->push($request);
        $token = $client->getContainer()->get('security.csrf.token_manager')->getToken('asesoria')->getValue();
        $request->getSession()->save();
        $client->getContainer()->get('request_stack')->pop();
        $client->request('POST', '/asesorias', [
            '_token' => $token,
            'name' => 'Otra Asesoría',
            'tax_id' => 'B0000002B',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'Perteneces a una asesoría como empleado');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertSelectorTextContains('h1', 'Norte Asesores');
        self::assertStringNotContainsString('Invitar empleado', (string) $client->getResponse()->getContent());
        $client->request('POST', '/asesorias/'.$firmId.'/invitar', [
            '_token' => 'caducado',
            'email' => 'otra@example.com',
        ]);
        self::assertResponseStatusCodeSame(404);

        $this->logout($client);
        $this->register($client, 'ajena-asesoria@example.com');
        $this->confirm($client);
        $this->login($client, 'ajena-asesoria@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Norte Asesores', (string) $client->getResponse()->getContent());
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

    private function latestInvitationPath(): string
    {
        $invitation = null;
        foreach (RecordingAccountMailer::$messages as $message) {
            if ('advisory-invitation' === $message['kind']) {
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
        $id = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT id FROM advisory_firm WHERE name = ?',
            [$name],
        );
        self::assertIsString($id);

        return $id;
    }
}
