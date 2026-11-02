<?php

declare(strict_types=1);

namespace App\Tests\Expense;

use App\Tests\Identity\ConfigurablePwnedPasswordList;
use App\Tests\Identity\PreparesAccountSchema;
use App\Tests\Identity\RecordingAccountMailer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ExpenseWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testElTitularRegistraElGastoYOtraCuentaNoLoVe(): void
    {
        $client = $this->client();
        $client->request('GET', '/gastos');
        self::assertResponseRedirects('/entrar');

        $this->register($client, 'gastos@example.com');
        $this->confirm($client);
        $this->login($client, 'gastos@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Norte',
            'tax_id' => 'B12345674',
        ]));
        $client->followRedirect();
        $client->clickLink('Ver gastos');
        self::assertSelectorTextContains('h1', 'Gastos');
        self::assertSelectorTextContains('main', 'Suministros');
        self::assertSelectorTextContains('main', 'Crea antes un proveedor');

        $client->request('POST', '/gastos', ['_token' => 'caducado']);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'El formulario ha caducado');

        $client->request('POST', '/gastos/categorias', [
            '_token' => $this->token($client, 'form[action$="/gastos/categorias"] input[name="_token"]'),
            'nombre' => 'Software',
        ]);
        self::assertResponseRedirects('/gastos?categoria=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Software');

        $client->clickLink('Contactos');
        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Papelería Norte',
            'nif' => 'B12345674',
            'proveedor' => '1',
        ]);
        self::assertResponseRedirects();
        $client->request('GET', '/gastos');
        $supplier = $client->getCrawler()->filter('#contacto option')->attr('value');
        $material = null;
        $options = $client->getCrawler()->filter('#categoria option');
        for ($index = 0; $index < $options->count(); ++$index) {
            $option = $options->eq($index);
            if ('Material' === trim($option->text())) {
                $material = $option->attr('value');
            }
        }
        self::assertIsString($supplier);
        self::assertIsString($material);
        $client->request('POST', '/gastos', [
            '_token' => $this->token($client, 'form[action$="/gastos"] input[name="_token"]'),
            'tipo' => 'factura',
            'contacto' => $supplier,
            'categoria' => $material,
            'metodo' => 'tarjeta',
            'numero' => 'G-1',
            'fecha' => '2026-11-02',
            'descripcion' => ['Folios', ''],
            'cantidad' => ['2', ''],
            'precio' => ['100,00', ''],
            'iva' => ['21', ''],
            'retencion' => ['0', ''],
        ]);
        self::assertResponseRedirects('/gastos?gasto=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'G-1');
        self::assertSelectorTextContains('main', 'Papelería Norte');
        self::assertSelectorTextContains('main', 'Material');
        self::assertSelectorTextContains('main', '200,00 €');
        self::assertSelectorTextContains('main', '42,00 €');
        self::assertSelectorTextContains('main', '242,00 €');

        $client->request('POST', '/gastos/categorias/'.$material.'/desactivar', [
            '_token' => $this->token($client, 'form[action$="/desactivar"] input[name="_token"]'),
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Desactivada');
        self::assertSelectorTextContains('main', 'G-1');
        self::assertSelectorTextContains('main', 'Material');
        $stillOffered = false;
        $options = $client->getCrawler()->filter('#categoria option');
        for ($index = 0; $index < $options->count(); ++$index) {
            if ('Material' === trim($options->eq($index)->text())) {
                $stillOffered = true;
            }
        }
        self::assertFalse($stillOffered);

        $this->logout($client);
        $this->register($client, 'otra.gastos@example.com');
        $this->confirm($client);
        $this->login($client, 'otra.gastos@example.com');
        $client->request('GET', '/gastos');
        self::assertSelectorTextContains('main', 'Elige antes una entidad fiscal');
        self::assertStringNotContainsString('G-1', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Papelería Norte', (string) $client->getResponse()->getContent());
        $client->request('POST', '/gastos/categorias/'.$material.'/desactivar', ['_token' => 'caducado']);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Papelería Norte', (string) $client->getResponse()->getContent());
    }

    public function testElTitularDecideLaDeducibilidadSinCambiarElImporte(): void
    {
        $client = $this->client();
        $this->register($client, 'deducible@example.com');
        $this->confirm($client);
        $this->login($client, 'deducible@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Deducible',
            'tax_id' => 'B12345674',
        ]));
        $client->followRedirect();
        $client->clickLink('Contactos');
        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Papelería Norte',
            'nif' => 'B12345674',
            'proveedor' => '1',
        ]);
        $client->followRedirect();
        $client->request('GET', '/gastos');
        $supplier = $client->getCrawler()->filter('#contacto option')->attr('value');
        $material = null;
        $options = $client->getCrawler()->filter('#categoria option');
        for ($index = 0; $index < $options->count(); ++$index) {
            $option = $options->eq($index);
            if ('Material' === trim($option->text())) {
                $material = $option->attr('value');
            }
        }
        self::assertIsString($supplier);
        self::assertIsString($material);
        $client->request('POST', '/gastos', [
            '_token' => $this->token($client, 'form[action$="/gastos"] input[name="_token"]'),
            'tipo' => 'factura',
            'contacto' => $supplier,
            'categoria' => $material,
            'metodo' => 'tarjeta',
            'numero' => 'G-9',
            'fecha' => '2026-11-02',
            'descripcion' => ['Folios'],
            'cantidad' => ['2'],
            'precio' => ['100,00'],
            'iva' => ['21'],
            'retencion' => ['0'],
        ]);
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Sin decidir');
        self::assertSelectorTextContains('main', '242,00 €');

        $form = $client->getCrawler()->selectButton('Guardar base')->form();
        $path = parse_url($form->getUri(), PHP_URL_PATH);
        self::assertIsString($path);
        $client->request('POST', $path, [
            '_token' => 'caducado',
            'parte' => 'base',
            'deducible' => '1',
            'motivo' => 'No debe guardarse',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'Sin decidir');
        self::assertStringNotContainsString('No debe guardarse', (string) $client->getResponse()->getContent());

        $client->submit($client->getCrawler()->selectButton('Guardar base')->form([
            'deducible' => '1',
            'motivo' => '   ',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Escribe el motivo');

        $client->submit($client->getCrawler()->selectButton('Guardar base')->form([
            'deducible' => '1',
            'motivo' => 'Papel del mostrador',
        ]));
        self::assertResponseRedirects('/gastos?deduccion=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Base: Deducible — Papel del mostrador');
        self::assertSelectorTextContains('main', 'Cuota: Sin decidir');
        self::assertSelectorTextContains('main', '242,00 €');

        $client->submit($client->getCrawler()->selectButton('Guardar cuota')->form([
            'deducible' => '0',
            'motivo' => 'La cuota no es deducible',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Cuota: No deducible — La cuota no es deducible');
        self::assertSelectorTextContains('main', 'Base: Deducible — Papel del mostrador');
        self::assertSelectorTextContains('main', '200,00 €');
        self::assertSelectorTextContains('main', '42,00 €');

        $this->logout($client);
        $this->register($client, 'otra.deducible@example.com');
        $this->confirm($client);
        $this->login($client, 'otra.deducible@example.com');
        $client->request('POST', $path, ['_token' => 'caducado', 'parte' => 'base', 'deducible' => '0', 'motivo' => 'Ajena']);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Papel del mostrador', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('G-9', (string) $client->getResponse()->getContent());
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

    private function token(KernelBrowser $client, string $selector = 'input[name="_token"]'): string
    {
        $token = $client->getCrawler()->filter($selector)->attr('value');
        self::assertIsString($token);

        return $token;
    }
}
