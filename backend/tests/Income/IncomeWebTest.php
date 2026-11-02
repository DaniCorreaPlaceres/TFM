<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Tests\Identity\ConfigurablePwnedPasswordList;
use App\Tests\Identity\PreparesAccountSchema;
use App\Tests\Identity\RecordingAccountMailer;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\FileFormField;
use Symfony\Component\DomCrawler\Form;

final class IncomeWebTest extends WebTestCase
{
    use PreparesAccountSchema;

    public function testElTitularRegistraElIngresoConCobroParcialYTrazabilidad(): void
    {
        $client = $this->client();
        $client->request('GET', '/ingresos');
        self::assertResponseRedirects('/entrar');

        $this->register($client, 'ingresos@example.com');
        $this->confirm($client);
        $this->login($client, 'ingresos@example.com');
        $client->followRedirect();
        $client->clickLink('Entidades');
        $client->submit($client->getCrawler()->selectButton('Crear entidad')->form([
            'legal_name' => 'Taller Norte',
            'tax_id' => 'B12345674',
        ]));
        $client->followRedirect();
        $client->clickLink('Ingresos y gastos');
        self::assertSelectorTextContains('main', 'Crea antes un cliente');

        $client->clickLink('Contactos');
        $token = $this->token($client);
        $client->request('POST', '/contactos', [
            '_token' => $token,
            'nombre' => 'Ana Ruiz',
            'nif' => '12345678A',
            'cliente' => '1',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'El NIF no es válido');

        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Ana Ruiz',
            'nif' => '12345678Z',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'cliente, proveedor o ambos');

        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Ana Ruiz',
            'nif' => ' 12345678-z ',
            'cliente' => '1',
            'proveedor' => '1',
        ]);
        self::assertResponseRedirects('/contactos?guardado=1');
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Ana Ruiz');
        self::assertSelectorTextContains('main', '12345678Z');

        $client->request('POST', '/contactos', [
            '_token' => $this->token($client),
            'nombre' => 'Ana Otra',
            'nif' => '12345678Z',
            'cliente' => '1',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Ya existe un contacto');

        $client->clickLink('Ingresos y gastos');
        $contact = $client->getCrawler()->filter('#contacto option')->attr('value');
        self::assertIsString($contact);
        $client->request('POST', '/ingresos', [
            '_token' => $this->token($client),
            'contacto' => $contact,
            'numero' => 'F-1',
            'fecha' => '2026-11-02',
            'descripcion' => ['Consultoría', 'Material'],
            'cantidad' => ['2', '1'],
            'precio' => ['100,00', '50,00'],
            'iva' => ['21', '10'],
            'retencion' => ['15', '0'],
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorTextContains('main', '267,00 €');
        self::assertSelectorTextContains('main', 'Pendiente');
        self::assertSelectorTextContains('main', 'Ana Ruiz');
        self::assertSelectorTextContains('main', 'Ingreso registrado');
        $income = $client->getRequest()->getPathInfo();

        $client->clickLink('Contactos');
        $client->submit($client->getCrawler()->selectButton('Guardar contacto')->form([
            'nombre' => 'Ana María',
            'nif' => '12345678Z',
            'cliente' => '1',
        ]));
        $client->followRedirect();
        $client->request('GET', $income);
        self::assertSelectorTextContains('main', 'Contacto en el ingreso: Ana Ruiz');

        $paymentToken = $client->getCrawler()->filter('input[name="payment_token"]')->attr('value');
        $version = $client->getCrawler()->filter('form[action$="/cobros"] input[name="version"]')->attr('value');
        self::assertIsString($paymentToken);
        self::assertIsString($version);
        $payment = [
            '_token' => $this->token($client, 'form[action$="/cobros"] input[name="_token"]'),
            'version' => $version,
            'payment_token' => $paymentToken,
            'importe' => '100,00',
            'fecha_cobro' => '2026-11-02',
            'metodo' => 'transferencia',
            'referencia' => 'AB1',
        ];
        $client->request('POST', $income.'/cobros', $payment);
        self::assertResponseRedirects($income.'?cobro=1');
        $client->request('POST', $income.'/cobros', $payment);
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Parcial');
        self::assertSelectorTextContains('main', '167,00 €');
        $content = (string) $client->getResponse()->getContent();
        self::assertSame(1, substr_count($content, '· 100,00 € ·'));
        self::assertStringNotContainsString('· 200,00 € ·', $content);

        $client->request('POST', $income.'/cobros', [
            '_token' => $this->token($client, 'form[action$="/cobros"] input[name="_token"]'),
            'version' => $client->getCrawler()->filter('form[action$="/cobros"] input[name="version"]')->attr('value'),
            'payment_token' => $client->getCrawler()->filter('input[name="payment_token"]')->attr('value'),
            'importe' => '167,01',
            'fecha_cobro' => '2026-11-02',
            'metodo' => 'efectivo',
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'supera el saldo');

        $client->request('POST', $income.'/cobros', [
            '_token' => $this->token($client, 'form[action$="/cobros"] input[name="_token"]'),
            'version' => $client->getCrawler()->filter('form[action$="/cobros"] input[name="version"]')->attr('value'),
            'payment_token' => $client->getCrawler()->filter('input[name="payment_token"]')->attr('value'),
            'importe' => '167',
            'fecha_cobro' => '2026-11-02',
            'metodo' => 'efectivo',
        ]);
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Cobrado');
        self::assertStringNotContainsString('Registrar cobro', (string) $client->getResponse()->getContent());

        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
        self::assertIsString($png);
        $directory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'aselto-income-test';
        if (!is_dir($directory)) {
            mkdir($directory);
        }
        $path = $directory.DIRECTORY_SEPARATOR.'nota.png';
        file_put_contents($path, $png);
        $form = $client->getCrawler()->selectButton('Adjuntar')->form();
        $this->upload($form, $path);
        $client->submit($form);
        $client->followRedirect();
        $client->clickLink('nota.png');
        self::assertSame($png, $client->getResponse()->getContent());
        unlink($path);

        $client->request('GET', $income);
        $client->submit($client->getCrawler()->selectButton('Anular ingreso')->form([
            'motivo' => 'Factura repetida',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Anulado');
        self::assertSelectorTextContains('main', 'Ingreso anulado');
        self::assertSelectorTextContains('main', 'Consultoría');
        self::assertSelectorTextContains('main', '100,00 €');

        $csvPath = tempnam(sys_get_temp_dir(), 'csv');
        self::assertIsString($csvPath);
        file_put_contents($csvPath, <<<'CSV'
fecha,numero,contacto,nif,descripcion,cantidad,precio,iva,retencion
15/01/2026,F-CSV,Luis Perez,00000000T,Informe,1,"10,00",21,0
15/01/2026,F-CSV,Luis Perez,00000000T,Extra,1,5,10,0
02/11/2026,F-MAL,Ana,12345678A,Malo,1,10,21,0
02/11/2026,F-1,Ana Ruiz,12345678Z,Otra,1,10,21,0
CSV);
        $client->request('GET', '/ingresos/importar');
        $import = $client->getCrawler()->selectButton('Importar')->form();
        $this->upload($import, $csvPath);
        $client->submit($import);
        self::assertSelectorTextContains('main', 'Se han registrado 1 ingresos');
        self::assertSelectorTextContains('main', 'El NIF no es válido');
        self::assertSelectorTextContains('main', 'Ya existe un ingreso con este número');
        unlink($csvPath);

        $client->request('GET', '/ingresos');
        $client->submit($client->getCrawler()->selectButton('Cerrar trimestre')->form([
            'anio' => '2026',
            'trimestre' => '4',
        ]));
        $client->followRedirect();
        self::assertSelectorTextContains('main', 'Cerrado: 2026 T4');
        $contact = $client->getCrawler()->filter('#contacto option')->attr('value');
        self::assertIsString($contact);
        $client->request('POST', '/ingresos', [
            '_token' => $this->token($client),
            'contacto' => $contact,
            'numero' => 'F-3',
            'fecha' => '2026-11-03',
            'descripcion' => ['Cerrado'],
            'cantidad' => ['1'],
            'precio' => ['10'],
            'iva' => ['21'],
            'retencion' => ['0'],
        ]);
        self::assertResponseStatusCodeSame(422);
        self::assertSelectorTextContains('.error', 'Este trimestre está cerrado');

        $client->request('POST', '/ingresos', [
            '_token' => $this->token($client),
            'contacto' => $contact,
            'numero' => 'F-2',
            'fecha' => '2026-01-20',
            'descripcion' => ['Enero'],
            'cantidad' => ['1'],
            'precio' => ['10'],
            'iva' => ['0'],
            'retencion' => ['0'],
        ]);
        self::assertResponseRedirects();
        $client->followRedirect();
        self::assertSelectorTextContains('main', '10,00 €');
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

    private function upload(Form $form, string $path): void
    {
        $field = $form['archivo'];
        self::assertInstanceOf(FileFormField::class, $field);
        $field->upload($path);
    }

    private function token(KernelBrowser $client, string $selector = 'input[name="_token"]'): string
    {
        $token = $client->getCrawler()->filter($selector)->attr('value');
        self::assertIsString($token);

        return $token;
    }
}
