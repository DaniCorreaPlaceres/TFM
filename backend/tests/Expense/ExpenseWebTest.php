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

    public function testElTitularAnotaElPagoYRevisaElGasto(): void
    {
        $client = $this->client();
        $this->register($client, 'revision@example.com');
        $this->confirm($client);
        $this->login($client, 'revision@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Revision',
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
            'numero' => 'G-8',
            'fecha' => '2026-11-02',
            'descripcion' => ['Folios'],
            'cantidad' => ['2'],
            'precio' => ['100,00'],
            'iva' => ['21'],
            'retencion' => ['0'],
        ]);
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Revisión: Borrador. No definitivo');
        self::assertSelectorTextContains('main', 'Pago: Pendiente. Anotado: 0,00 €. Saldo: 242,00 €');
        self::assertSelectorTextContains('main', '200,00 €');
        self::assertSelectorTextContains('main', '42,00 €');

        $form = $client->getCrawler()->selectButton('Anotar pago')->form();
        $payPath = parse_url($form->getUri(), PHP_URL_PATH);
        self::assertIsString($payPath);
        $client->request('POST', $payPath, [
            '_token' => 'caducado',
            'payment_token' => '00000000-0000-7000-8000-000000000001',
            'importe' => '999,00',
            'fecha' => '2026-11-02',
            'metodo' => 'transferencia',
            'referencia' => 'No debe guardarse',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('main', 'Pago: Pendiente');
        self::assertStringNotContainsString('No debe guardarse', (string) $client->getResponse()->getContent());

        $form = $client->getCrawler()->selectButton('Anotar pago')->form([
            'importe' => '100,00',
            'fecha' => '2026-11-02',
            'metodo' => 'transferencia',
            'referencia' => 'P-1',
        ]);
        $repeated = $form->getPhpValues();
        $client->submit($form);
        self::assertResponseRedirects('/gastos?pago=1');
        $client->followRedirect();
        $client->request('POST', $payPath, $repeated);
        self::assertResponseRedirects('/gastos?pago=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Pago: Parcial. Anotado: 100,00 €. Saldo: 142,00 €');
        self::assertSelectorTextContains('main', 'base 200,00 €');
        self::assertSelectorTextContains('main', 'cuota 42,00 €');
        self::assertSelectorTextContains('main', 'total 242,00 €');

        $client->submit($client->getCrawler()->selectButton('Anotar pago')->form([
            'importe' => '200,00',
            'fecha' => '2026-11-02',
            'metodo' => 'tarjeta',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'supera el saldo');
        self::assertSelectorTextContains('main', 'Saldo: 142,00 €');

        $client->submit($client->getCrawler()->selectButton('Anotar pago')->form([
            'importe' => '142,00',
            'fecha' => '2026-11-02',
            'metodo' => 'tarjeta',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Pago: Pagado. Anotado: 242,00 €. Saldo: 0,00 €');
        self::assertCount(0, $client->getCrawler()->filter('button:contains("Anotar pago")'));

        $client->submit($client->getCrawler()->selectButton('Guardar revisión')->form([
            'estado' => 'revisado',
            'motivo' => '',
        ]));
        self::assertResponseRedirects('/gastos?revision=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Revisión: Revisado. Definitivo');
        self::assertStringNotContainsString('No definitivo', (string) $client->getResponse()->getContent());
        self::assertSelectorTextContains('main', 'total 242,00 €');

        $review = $client->getCrawler()->filter('form[action$="/revision"]');
        $reviewPath = parse_url($review->attr('action'), PHP_URL_PATH);
        $reviewToken = $review->filter('input[name="_token"]')->attr('value');
        self::assertIsString($reviewPath);
        self::assertIsString($reviewToken);
        $client->request('POST', $reviewPath, [
            '_token' => $reviewToken,
            'estado' => 'rechazado',
            'motivo' => 'No procede',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'transición de revisión');
        self::assertSelectorTextContains('main', 'Definitivo');

        $client->submit($client->getCrawler()->selectButton('Guardar revisión')->form([
            'estado' => 'anulado',
            'motivo' => '   ',
        ]));
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Escribe el motivo de la revisión');

        $client->submit($client->getCrawler()->selectButton('Guardar revisión')->form([
            'estado' => 'anulado',
            'motivo' => 'Factura duplicada',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Revisión: Anulado. No vigente — Factura duplicada');
        self::assertSelectorTextContains('main', 'Pago: Pagado. Anotado: 242,00 €. Saldo: 0,00 €');
        self::assertCount(0, $client->getCrawler()->filter('button:contains("Guardar base")'));
        self::assertCount(0, $client->getCrawler()->filter('button:contains("Anotar pago")'));

        $this->logout($client);
        $this->register($client, 'otra.revision@example.com');
        $this->confirm($client);
        $this->login($client, 'otra.revision@example.com');
        $client->request('POST', $payPath, ['_token' => 'caducado', 'importe' => '1,00']);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('Factura duplicada', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('G-8', (string) $client->getResponse()->getContent());
    }

    public function testElDuplicadoNoBloqueaYLaPropuestaQuedaEnBorrador(): void
    {
        $client = $this->client();
        $this->register($client, 'duplicado@example.com');
        $this->confirm($client);
        $this->login($client, 'duplicado@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Duplicado',
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
        $payload = [
            'tipo' => 'factura',
            'contacto' => $supplier,
            'categoria' => $material,
            'metodo' => 'tarjeta',
            'numero' => 'G-7',
            'fecha' => '2026-11-02',
            'descripcion' => ['Folios'],
            'cantidad' => ['2'],
            'precio' => ['100,00'],
            'iva' => ['21'],
            'retencion' => ['0'],
        ];
        $client->request('POST', '/gastos', $payload + [
            '_token' => $this->token($client, 'form[action$="/gastos"] input[name="_token"]'),
        ]);
        $client->followRedirect();
        self::assertSelectorTextNotContains('main', 'Posible duplicado');

        $second = $payload;
        $second['_token'] = $this->token($client, 'form[action$="/gastos"] input[name="_token"]');
        $second['fecha'] = '2026-11-03';
        $second['precio'] = ['10,00'];
        $second['cantidad'] = ['1'];
        $second['iva'] = ['0'];
        $client->request('POST', '/gastos', $second);
        self::assertResponseRedirects('/gastos?gasto=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Posible duplicado: G-7');
        self::assertSelectorTextContains('main', '242,00 €');
        self::assertSelectorTextContains('main', '10,00 €');

        $form = $client->getCrawler()->selectButton('Marcar como mensual')->form();
        $recurrencePath = parse_url($form->getUri(), PHP_URL_PATH);
        self::assertIsString($recurrencePath);
        $client->request('POST', $recurrencePath, ['_token' => 'caducado', 'periodo' => 'mensual']);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextNotContains('main', 'Mensual.');

        $client->submit($client->getCrawler()->selectButton('Marcar como mensual')->form());
        self::assertResponseRedirects('/gastos?recurrencia=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Mensual.');

        $client->submit($client->getCrawler()->selectButton('Crear propuesta')->form());
        self::assertResponseRedirects('/gastos?propuesta=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'G-7-P');
        self::assertSelectorTextContains('main', 'Propuesta de G-7');
        self::assertSelectorTextContains('main', '03/12/2026');
        self::assertSelectorTextContains('main', 'No es definitiva hasta revisarla');
        self::assertSame(1, substr_count((string) $client->getResponse()->getContent(), 'G-7-P'));

        $proposalForm = $client->getCrawler()->selectButton('Crear propuesta')->form();
        $proposalPath = parse_url($proposalForm->getUri(), PHP_URL_PATH);
        self::assertIsString($proposalPath);
        $client->submit($proposalForm);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Ya hay una propuesta');
        self::assertSame(1, substr_count((string) $client->getResponse()->getContent(), 'G-7-P'));

        $this->logout($client);
        $this->register($client, 'otra.duplicado@example.com');
        $this->confirm($client);
        $this->login($client, 'otra.duplicado@example.com');
        $client->request('POST', $proposalPath, ['_token' => 'caducado']);
        self::assertResponseStatusCodeSame(404);
        self::assertStringNotContainsString('G-7-P', (string) $client->getResponse()->getContent());
        self::assertStringNotContainsString('Papelería Norte', (string) $client->getResponse()->getContent());
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
