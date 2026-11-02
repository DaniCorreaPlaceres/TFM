<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdvisoryEmployeePermissionWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testLaEmpleadaSoloVeLasEntidadesSiLaAdministradoraSeLoConcede(): void
    {
        $client = $this->client();
        $this->register($client, 'admin-permiso@example.com');
        $this->confirm($client);
        $this->login($client, 'admin-permiso@example.com');
        $client->followRedirect();
        $client->request('GET', '/asesorias');
        $client->submit($client->getCrawler()->selectButton('Crear asesoría')->form([
            'name' => 'Norte Permisos',
            'tax_id' => 'B0000005B',
        ]));
        $client->followRedirect();
        $firmId = $this->firmId('Norte Permisos');
        $client->submit($client->getCrawler()->filter('form[action$="/invitar"]')->form([
            'email' => 'empleada-permiso@example.com',
        ]));
        $client->followRedirect();
        $client->submit($client->getCrawler()->selectButton('Invitar entidad')->form([
            'entity_email' => 'titular-permiso@example.com',
        ]));
        $invitation = $this->latestInvitationPath();

        $this->logout($client);
        $this->register($client, 'empleada-permiso@example.com');
        $this->confirm($client);
        $this->register($client, 'titular-permiso@example.com');
        $this->confirm($client);
        $this->login($client, 'titular-permiso@example.com');
        $client->request('GET', '/entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Permiso',
            'tax_id' => 'B0000006B',
        ]));
        $client->request('GET', $invitation);
        $target = $client->getCrawler()->filter('input[name="target"]')->attr('value');
        self::assertIsString($target);
        $client->submit($client->getCrawler()->selectButton('Aceptar vínculo')->form([
            'target' => $target,
        ]));

        $this->logout($client);
        $this->login($client, 'empleada-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        $body = (string) $client->getResponse()->getContent();
        self::assertStringNotContainsString('Taller Permiso', $body);
        self::assertStringNotContainsString('Invitar entidad', $body);
        self::assertStringNotContainsString('Conceder ver entidades', $body);
        $client->request('POST', '/asesorias/'.$firmId.'/permiso-entidades', [
            '_token' => 'caducado',
            'membership_id' => 'ajeno',
            'granted' => '1',
        ]);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Taller Permiso', (string) $client->getResponse()->getContent());

        $this->logout($client);
        $this->login($client, 'admin-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        $membershipId = $client->getCrawler()->filter('input[name="membership_id"]')->attr('value');
        self::assertIsString($membershipId);
        $client->request('POST', '/asesorias/'.$firmId.'/permiso-entidades', [
            '_token' => 'caducado',
            'membership_id' => $membershipId,
            'granted' => '1',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'El formulario ha caducado');
        self::assertStringNotContainsString('ve las entidades', (string) $client->getResponse()->getContent());

        $client->submit($client->getCrawler()->selectButton('Conceder ver entidades')->form());
        self::assertResponseRedirects('/asesorias/'.$firmId.'?permiso=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'El permiso ha quedado guardado');
        self::assertSelectorTextContains('main', 've las entidades');

        $this->logout($client);
        $this->login($client, 'empleada-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertSelectorTextContains('main', 'Taller Permiso');
        self::assertStringNotContainsString('Invitar entidad', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Conceder ver entidades', (string) $client->getResponse()->getContent());
        $client->request('POST', '/asesorias/'.$firmId.'/invitar-entidad', [
            '_token' => 'caducado',
            'entity_email' => 'otra@example.com',
        ]);
        self::assertResponseStatusCodeSame(404);

        $this->logout($client);
        $this->login($client, 'ajena-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Taller Permiso', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Norte Permisos', (string) $client->getResponse()->getContent());

        $this->logout($client);
        $this->login($client, 'admin-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        $client->submit($client->getCrawler()->selectButton('Retirar ver entidades')->form());
        $client->followRedirect();
        $this->logout($client);
        $this->login($client, 'empleada-permiso@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertStringNotContainsString('Taller Permiso', (string) $client->getResponse()->getContent());
    }

    private function client(): KernelBrowser
    {
        $client = static::createClient();
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
        if ('ajena-permiso@example.com' === $email) {
            $this->register($client, $email);
            $this->confirm($client);
        }
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
        $id = static::getContainer()->get(Connection::class)->fetchOne(
            'SELECT id FROM advisory_firm WHERE name = ?',
            [$name],
        );
        self::assertIsString($id);

        return $id;
    }
}
