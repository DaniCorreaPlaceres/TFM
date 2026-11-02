<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class AdvisoryClientWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testElVinculoFuncionaEnLosDosSentidosYSoloHayUnaAsesoriaActiva(): void
    {
        $client = $this->client();
        $this->register($client, 'titular-vinculo@example.com');
        $this->confirm($client);
        $this->login($client, 'titular-vinculo@example.com');
        $client->followRedirect();
        $client->request('GET', '/entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Norte',
            'tax_id' => 'b-0000001b',
        ]));
        $client->followRedirect();
        $client->request('GET', '/entidades');
        $client->request('POST', '/entidades/invitar-asesoria', [
            '_token' => 'caducado',
            'advisory_email' => 'admin-vinculo@example.com',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'El formulario ha caducado');
        self::assertSame(0, $this->clientInvitations());

        $client->submit($client->getCrawler()->selectButton('Invitar asesoría')->form([
            'advisory_email' => 'admin-vinculo@example.com',
        ]));
        self::assertResponseRedirects('/entidades?asesoria=1');
        $invitation = $this->latestInvitationPath();

        $this->logout($client);
        $this->register($client, 'admin-vinculo@example.com');
        $this->confirm($client);
        $this->login($client, 'admin-vinculo@example.com');
        $client->request('GET', $invitation);
        self::assertSelectorTextContains('main', 'Taller Norte');
        self::assertSelectorTextContains('main', 'Crea una asesoría');

        $this->logout($client);
        $this->login($client, 'titular-vinculo@example.com');
        $client->request('GET', $invitation);
        self::assertSelectorTextContains('main', 'Esta invitación es para otro correo');
        self::assertStringNotContainsString('Taller Norte', (string) $client->getResponse()->getContent());

        $this->logout($client);
        $this->login($client, 'admin-vinculo@example.com');
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
        $client->request('POST', $invitation, [
            '_token' => 'caducado',
            'target' => $target,
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'El formulario ha caducado');
        $client->request('GET', $invitation);
        $client->submit($client->getCrawler()->selectButton('Aceptar vínculo')->form([
            'target' => $target,
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('h1', 'Norte Asesores');
        self::assertSelectorTextContains('main', 'Taller Norte');

        $this->logout($client);
        $this->login($client, 'titular-vinculo@example.com');
        $client->request('GET', '/sesion');
        self::assertSelectorTextContains('main', 'Asesoría activa: Norte Asesores');
        $client->request('GET', '/entidades');
        self::assertSelectorTextContains('main', 'Asesoría activa: Norte Asesores');
        self::assertStringNotContainsString('Invitar asesoría', (string) $client->getResponse()->getContent());

        $this->logout($client);
        $this->login($client, 'admin-vinculo@example.com');
        $crawler = $client->request('GET', '/asesorias/'.$firmId);
        $client->submit($crawler->filter('form[action$="/invitar"]')->form([
            'email' => 'empleada-vinculo@example.com',
        ]));
        $this->logout($client);
        $this->register($client, 'empleada-vinculo@example.com');
        $this->confirm($client);
        $this->login($client, 'empleada-vinculo@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertStringNotContainsString('Taller Norte', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Invitar entidad', (string) $client->getResponse()->getContent());
        $client->request('POST', '/asesorias/'.$firmId.'/invitar-entidad', [
            '_token' => 'caducado',
            'entity_email' => 'otra@example.com',
        ]);
        self::assertResponseStatusCodeSame(404);

        $this->logout($client);
        $this->register($client, 'titular-sur@example.com');
        $this->confirm($client);
        $this->login($client, 'titular-sur@example.com');
        $client->request('GET', '/entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Sur',
            'tax_id' => 'B0000003B',
        ]));
        $this->logout($client);
        $this->login($client, 'admin-vinculo@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        $client->submit($client->getCrawler()->selectButton('Invitar entidad')->form([
            'entity_email' => 'titular-sur@example.com',
        ]));
        self::assertResponseRedirects('/asesorias/'.$firmId.'?entidad=1');
        $secondInvitation = $this->latestInvitationPath();
        $this->logout($client);
        $this->login($client, 'titular-sur@example.com');
        $client->request('GET', $secondInvitation);
        self::assertSelectorTextContains('main', 'Norte Asesores');
        $entityTarget = $client->getCrawler()->filter('input[name="target"]')->attr('value');
        self::assertIsString($entityTarget);
        $client->submit($client->getCrawler()->selectButton('Aceptar vínculo')->form([
            'target' => $entityTarget,
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Asesoría activa: Norte Asesores');

        $this->logout($client);
        $this->register($client, 'admin-sur@example.com');
        $this->confirm($client);
        $this->login($client, 'admin-sur@example.com');
        $client->request('GET', '/asesorias');
        $client->submit($client->getCrawler()->selectButton('Crear asesoría')->form([
            'name' => 'Sur Asesores',
            'tax_id' => 'B0000004B',
        ]));
        $client->followRedirect();
        $client->submit($client->getCrawler()->selectButton('Invitar entidad')->form([
            'entity_email' => 'titular-sur@example.com',
        ]));
        $thirdInvitation = $this->latestInvitationPath();
        $this->logout($client);
        $this->login($client, 'titular-sur@example.com');
        $client->request('GET', $thirdInvitation);
        $blocked = $client->getCrawler()->filter('input[name="target"]')->attr('value');
        self::assertIsString($blocked);
        $client->submit($client->getCrawler()->selectButton('Aceptar vínculo')->form([
            'target' => $blocked,
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'Esta entidad ya tiene una asesoría activa');

        $this->logout($client);
        $this->register($client, 'ajena-vinculo@example.com');
        $this->confirm($client);
        $this->login($client, 'ajena-vinculo@example.com');
        $client->request('GET', '/asesorias/'.$firmId);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Taller Norte', (string) $client->getResponse()->getContent());
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
            if ('advisory-client-invitation' === $message['kind']) {
                $invitation = $message;
            }
        }
        self::assertNotNull($invitation);
        $path = parse_url($invitation['url'], PHP_URL_PATH);
        self::assertIsString($path);

        return $path;
    }

    private function clientInvitations(): int
    {
        $count = 0;
        foreach (RecordingAccountMailer::$messages as $message) {
            if ('advisory-client-invitation' === $message['kind']) {
                ++$count;
            }
        }

        return $count;
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
