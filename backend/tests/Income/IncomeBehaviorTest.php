<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Income\Amount;
use App\Income\AnnulIncome;
use App\Income\AttachIncomeFile;
use App\Income\CivilDate;
use App\Income\CloseFiscalQuarter;
use App\Income\CollectIncome;
use App\Income\CorrectIncome;
use App\Income\ImportIncomes;
use App\Income\IncomeAccess;
use App\Income\IncomeCsv;
use App\Income\InvalidIncome;
use App\Income\RegisterIncome;
use App\Income\SaveContact;
use App\Income\SpanishTaxId;
use App\Tests\Identity\InMemoryFiscalEntityMembershipRepository;
use App\Tests\Identity\InMemoryRegistrationStore;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class IncomeBehaviorTest extends TestCase
{
    public function testElNifYLosImportesSiguenReglasExplicitas(): void
    {
        self::assertSame('12345678Z', SpanishTaxId::canonical('12345678-z'));
        self::assertSame('X1234567L', SpanishTaxId::canonical('x1234567l'));
        self::assertSame('B12345674', SpanishTaxId::canonical('ES B12345674'));
        self::assertSame('P1234567D', SpanishTaxId::canonical('P1234567D'));
        try {
            SpanishTaxId::canonical('12345678A');
            self::fail('Un NIF con la letra mal no es válido.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::TAX_ID, $exception->reason);
        }

        $line = Amount::line('2', '100,00', '21', '15');
        self::assertSame('200.00', $line->base);
        self::assertSame('42.00', $line->tax);
        self::assertSame('30.00', $line->withholding);
        self::assertSame('212.00', $line->total);

        $rounded = Amount::line('1', '10.0050', '0', '0');
        self::assertSame('10.01', $rounded->base);
        $half = Amount::line('1', '0.05', '10', '0');
        self::assertSame('0.05', $half->base);
        self::assertSame('0.01', $half->tax);
        self::assertSame('0.06', $half->total);
    }

    public function testElIngresoConservaLaCopiaYElCobroNoSuperaElSaldo(): void
    {
        [$account, $entity, $save, $register, $correct, $collect, $annul, $attach, $close, $import, $actions, $objects] = $this->services();
        $contact = $save->create($account, $entity, 'Ana Ruiz', '12345678z', true, true);
        self::assertTrue($contact->isClient());
        self::assertTrue($contact->isSupplier());
        try {
            $save->create($account, $entity, 'Otra', '12345678Z', true, false);
            self::fail('No puede haber dos contactos con el mismo NIF.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::EXISTS, $exception->reason);
        }

        $issued = $this->date('2026-11-02');
        $income = $register($account, $entity, $contact->id(), 'F-1', $issued, null, [
            $this->row('Consultoría', '2', '100,00', '21', '15'),
            $this->row('Material', '1', '50', '10', '0'),
        ]);
        self::assertSame('267.00', $income->total());
        self::assertSame('250.00', $income->base());
        self::assertSame('47.00', $income->tax());
        self::assertSame('30.00', $income->withholding());
        $frozen = $income->snapshotName();
        self::assertSame('Ana Ruiz', $frozen);
        self::assertSame('pendiente', $income->paymentStatus());

        $save->update($account, $entity, $contact->id(), 'Ana María', '12345678Z', true, false);
        self::assertSame('Ana María', $contact->displayName());
        self::assertSame($frozen, $income->snapshotName());
        $correct($account, $entity, $income->id(), $income->version(), $contact->id(), 'F-1', $issued, null, [
            $this->row('Consultoría', '2', '100,00', '21', '15'),
            $this->row('Material', '1', '50', '10', '0'),
        ]);
        self::assertNotSame($contact->displayName(), $income->snapshotName());

        $collect($account, $entity, $income->id(), $income->version(), '100,00', $issued, 'transferencia', 'AB1', '11111111-1111-1111-1111-111111111111');
        $collect($account, $entity, $income->id(), 1, '100,00', $issued, 'transferencia', 'AB1', '11111111-1111-1111-1111-111111111111');
        self::assertSame('100.00', $income->collected());
        self::assertSame('parcial', $income->paymentStatus());
        self::assertCount(1, array_filter($income->payments(), static fn ($payment): bool => '100.00' === $payment->amount()));
        try {
            $collect($account, $entity, $income->id(), $income->version(), '167,01', $issued, 'efectivo', '', '22222222-2222-2222-2222-222222222222');
            self::fail('Un cobro no puede superar el saldo.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::EXCEEDS, $exception->reason);
        }
        self::assertSame('167.00', $income->balance());
        $collect($account, $entity, $income->id(), $income->version(), '167', $issued, 'efectivo', '', '22222222-2222-2222-2222-222222222222');
        self::assertSame('cobrado', $income->paymentStatus());

        $attach($account, $entity, $income->id(), $income->version(), 'nota.pdf', 'application/pdf', "%PDF-1.4\nnota-privada");
        $stored = array_values($objects::$objects);
        self::assertCount(1, $stored);
        self::assertStringContainsString('nota-privada', $stored[0]);

        $annul($account, $entity, $income->id(), $income->version(), 'Factura repetida');
        self::assertSame('anulado', $income->paymentStatus());
        self::assertCount(2, $income->lines());
        self::assertCount(2, $income->payments());
        $summaries = array_map(static fn ($action): string => $action->summary(), $actions->forIncome($income));
        self::assertContains('Ingreso anulado.', $summaries);
        try {
            $collect($account, $entity, $income->id(), $income->version(), '1', $issued, 'efectivo', '', '33333333-3333-3333-3333-333333333333');
            self::fail('Un ingreso anulado no admite otro cobro.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::ANNULLED, $exception->reason);
        }

        $close($account, $entity, 2026, 4);
        try {
            $register($account, $entity, $contact->id(), 'F-3', $issued, null, [$this->row('Cerrado', '1', '10', '21', '0')]);
            self::fail('Un trimestre cerrado no admite el ingreso.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::CLOSED, $exception->reason);
        }

        $report = $import($account, $entity, <<<'CSV'
fecha,numero,contacto,nif,descripcion,cantidad,precio,iva,retencion
15/01/2026,F-CSV,Luis Perez,00000000T,Informe,1,"10,00",21,0
15/01/2026,F-CSV,Luis Perez,00000000T,Extra,1,5,10,0
02/11/2026,F-MAL,Ana,12345678A,Malo,1,10,21,0
02/11/2026,F-1,Ana Ruiz,12345678Z,Otra,1,10,21,0
CSV);
        self::assertSame(1, $report->imported);
        self::assertSame('El NIF no es válido.', $report->errors[0]['message']);
        self::assertSame('Este trimestre está cerrado.', $report->errors[1]['message']);
    }

    public function testOtraCuentaNoOperaElIngreso(): void
    {
        [$account, $entity, $save, $register] = $this->services();
        $contact = $save->create($account, $entity, 'Ana Ruiz', '12345678Z', true, false);
        $income = $register($account, $entity, $contact->id(), 'F-1', $this->date('2026-11-02'), null, [
            $this->row('Consultoría', '1', '10', '21', '0'),
        ]);
        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $otherEntity = FiscalEntity::open('Taller Sur', 'B12345674', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        try {
            $register($other, $entity, $contact->id(), 'F-2', $this->date('2026-01-15'), null, [
                $this->row('Ajena', '1', '10', '0', '0'),
            ]);
            self::fail('Otra cuenta no registra en la entidad.');
        } catch (InvalidIncome $exception) {
            self::assertSame(InvalidIncome::FORBIDDEN, $exception->reason);
        }
        self::assertSame('F-1', $income->invoiceNo());
        self::assertNotSame($entity->id(), $otherEntity->id());
    }

    /**
     * @return array{0: Account, 1: FiscalEntity, 2: SaveContact, 3: RegisterIncome, 4: CorrectIncome, 5: CollectIncome, 6: AnnulIncome, 7: AttachIncomeFile, 8: CloseFiscalQuarter, 9: ImportIncomes, 10: InMemoryIncomeActionRepository, 11: MemoryObjectStore}
     */
    private function services(): array
    {
        $contacts = new InMemoryContactRepository();
        $incomes = new InMemoryIncomeRepository();
        $actions = new InMemoryIncomeActionRepository();
        $attachments = new InMemoryIncomeAttachmentRepository();
        $quarters = new InMemoryClosedQuarterRepository();
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $store = new InMemoryRegistrationStore();
        $clock = new MockClock('2026-11-02T10:00:00Z');
        $access = new IncomeAccess($memberships, $incomes, $quarters);
        $objects = new MemoryObjectStore();
        $account = Account::register('titular@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $entity = FiscalEntity::open('Taller Norte', 'B12345674', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $memberships->save(FiscalEntityMembership::hold($entity, $account));

        return [
            $account,
            $entity,
            new SaveContact($access, $contacts, $store, $clock),
            new RegisterIncome($access, $contacts, $incomes, $actions, $store, $clock),
            new CorrectIncome($access, $contacts, $incomes, $actions, $store, $clock),
            new CollectIncome($access, $incomes, $actions, $store, $clock),
            new AnnulIncome($access, $incomes, $actions, $store, $clock),
            new AttachIncomeFile($access, $incomes, $attachments, $actions, $objects, $store, $clock),
            new CloseFiscalQuarter($access, $quarters, $store, $clock),
            new ImportIncomes($access, $contacts, $incomes, $actions, $store, new IncomeCsv(), $clock),
            $actions,
            $objects,
        ];
    }

    private function date(string $value): \DateTimeImmutable
    {
        $date = CivilDate::parse($value);
        self::assertNotNull($date);

        return $date;
    }

    /**
     * @return array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}
     */
    private function row(string $description, string $quantity, string $price, string $tax, string $withholding): array
    {
        return [
            'description' => $description,
            'quantity' => $quantity,
            'unitPrice' => $price,
            'taxRate' => $tax,
            'withholdingRate' => $withholding,
        ];
    }
}
