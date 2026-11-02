<?php

declare(strict_types=1);

namespace App\Tests\Expense;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Expense\DeactivateExpenseCategory;
use App\Expense\DecideExpenseDeduction;
use App\Expense\DefaultExpenseCategories;
use App\Expense\EnsureExpenseCategories;
use App\Expense\ExpenseAccess;
use App\Expense\ExpenseDuplicates;
use App\Expense\InvalidExpense;
use App\Expense\MarkExpenseRecurrence;
use App\Expense\NextMonth;
use App\Expense\PayExpense;
use App\Expense\ProposalNumber;
use App\Expense\ProposeExpense;
use App\Expense\RegisterExpense;
use App\Expense\ReviewExpense;
use App\Expense\SaveExpenseCategory;
use App\Tests\Identity\InMemoryFiscalEntityMembershipRepository;
use App\Tests\Identity\InMemoryRegistrationStore;
use App\Tests\Income\InMemoryContactRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

final class ExpenseBehaviorTest extends TestCase
{
    public function testLaCategoriaUsadaSeDesactivaYElGastoCuadra(): void
    {
        [$account, $entity, $ensure, $save, $deactivate, $register, $categories, $contacts] = $this->services();
        ($ensure)($entity);
        self::assertCount(count(DefaultExpenseCategories::NAMES), $categories->allForEntity($entity));
        ($ensure)($entity);
        self::assertCount(6, $categories->allForEntity($entity));

        $custom = ($save)($account, $entity, '  software   de   gestión ');
        self::assertSame('software de gestión', $custom->name());
        self::assertFalse($custom->isDefault());
        try {
            ($save)($account, $entity, 'SOFTWARE DE GESTIÓN');
            self::fail('El nombre no se puede repetir.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::EXISTS, $exception->reason);
        }

        $supplier = Contact::open($entity, 'Papelería Norte', 'B12345674', false, true, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($supplier);
        $clientOnly = Contact::open($entity, 'Ana Ruiz', '12345678Z', true, false, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($clientOnly);
        $material = $this->named($categories->allForEntity($entity), 'Material');

        $expense = $register(
            $account,
            $entity,
            $supplier->id(),
            $material->id(),
            'factura',
            'tarjeta',
            'G-1',
            new \DateTimeImmutable('2026-11-02'),
            [
                $this->row('Folios', '2', '100,00', '21', '0'),
                $this->row('Sobres', '1', '50', '10', '0'),
            ],
        );
        self::assertSame('250.00', $expense->base());
        self::assertSame('47.00', $expense->tax());
        self::assertSame('0.00', $expense->withholding());
        self::assertSame('297.00', $expense->total());
        self::assertSame('Papelería Norte', $expense->snapshotName());
        self::assertSame('Material', $expense->snapshotCategory());

        ($deactivate)($account, $entity, $material->id());
        self::assertFalse($material->isActive());
        self::assertSame('Material', $expense->snapshotCategory());
        try {
            $register($account, $entity, $supplier->id(), $material->id(), 'ticket', 'efectivo', 'G-2', new \DateTimeImmutable('2026-11-02'), [
                $this->row('Folios', '1', '10', '0', '0'),
            ]);
            self::fail('Una categoría desactivada no sirve para un gasto nuevo.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::CATEGORY, $exception->reason);
        }
        try {
            $register($account, $entity, $clientOnly->id(), $custom->id(), 'otro', 'otro', 'G-3', new \DateTimeImmutable('2026-11-02'), [
                $this->row('Folios', '1', '10', '0', '0'),
            ]);
            self::fail('Un cliente que no es proveedor no vale.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::CONTACT, $exception->reason);
        }

        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        try {
            ($deactivate)($other, $entity, $custom->id());
            self::fail('Otra cuenta no desactiva la categoría.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FORBIDDEN, $exception->reason);
        }
        self::assertTrue($custom->isActive());
        self::assertSame('G-1', $expense->documentNo());
    }

    public function testLaBaseYLaCuotaSeDecidenPorSeparado(): void
    {
        [$account, $entity, $ensure, $register, $decide, $categories, $contacts] = $this->deduction();
        ($ensure)($entity);
        $supplier = Contact::open($entity, 'Papelería Norte', 'B12345674', false, true, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($supplier);
        $material = $this->named($categories->allForEntity($entity), 'Material');
        $expense = $register($account, $entity, $supplier->id(), $material->id(), 'factura', 'tarjeta', 'G-1', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Folios', '2', '100,00', '21', '0'),
        ]);
        self::assertNull($expense->baseDeductible());
        self::assertNull($expense->taxDeductible());
        self::assertSame('200.00', $expense->base());
        self::assertSame('42.00', $expense->tax());
        self::assertSame('242.00', $expense->total());

        $decide($account, $entity, $expense->id(), 'base', true, '  Papel del mostrador  ');
        self::assertTrue($expense->baseDeductible());
        self::assertSame('Papel del mostrador', $expense->baseReason());
        self::assertNull($expense->taxDeductible());
        self::assertSame('242.00', $expense->total());

        try {
            $decide($account, $entity, $expense->id(), 'cuota', false, '   ');
            self::fail('El motivo vacío no se guarda.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::REASON, $exception->reason);
        }
        self::assertNull($expense->taxDeductible());

        $decide($account, $entity, $expense->id(), 'cuota', false, 'La cuota no es deducible');
        self::assertFalse($expense->taxDeductible());
        self::assertSame('La cuota no es deducible', $expense->taxReason());
        self::assertTrue($expense->baseDeductible());
        self::assertSame('200.00', $expense->base());
        self::assertSame('42.00', $expense->tax());

        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        try {
            $decide($other, $entity, $expense->id(), 'base', false, 'No es su gasto');
            self::fail('Otra cuenta no decide la deducibilidad.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FORBIDDEN, $exception->reason);
        }
        self::assertTrue($expense->baseDeductible());
        self::assertSame('Papel del mostrador', $expense->baseReason());
    }

    public function testElPagoYLaRevisionNoCambianElImporte(): void
    {
        [$account, $entity, $ensure, $register, $decide, $pay, $review, $categories, $contacts] = $this->revision();
        ($ensure)($entity);
        $supplier = Contact::open($entity, 'Papelería Norte', 'B12345674', false, true, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($supplier);
        $material = $this->named($categories->allForEntity($entity), 'Material');
        $expense = $register($account, $entity, $supplier->id(), $material->id(), 'factura', 'tarjeta', 'G-3', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Folios', '2', '100,00', '21', '0'),
        ]);
        self::assertSame('borrador', $expense->reviewState());
        self::assertFalse($expense->isDefinitive());
        self::assertSame('pendiente', $expense->paymentStatus());
        self::assertSame('0.00', $expense->paid());
        self::assertSame('242.00', $expense->balance());

        $token = Uuid::v7()->toRfc4122();
        $pay($account, $entity, $expense->id(), '100,00', new \DateTimeImmutable('2026-11-02'), 'transferencia', 'P-1', $token);
        $pay($account, $entity, $expense->id(), '100,00', new \DateTimeImmutable('2026-11-02'), 'transferencia', 'P-1', $token);
        self::assertSame('parcial', $expense->paymentStatus());
        self::assertSame('100.00', $expense->paid());
        self::assertSame('142.00', $expense->balance());
        self::assertSame('200.00', $expense->base());
        self::assertSame('42.00', $expense->tax());
        self::assertNull($expense->baseDeductible());
        try {
            $pay($account, $entity, $expense->id(), '200,00', new \DateTimeImmutable('2026-11-02'), 'tarjeta', '', Uuid::v7()->toRfc4122());
            self::fail('El pago no puede superar el saldo.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::EXCEEDS, $exception->reason);
        }
        self::assertSame('142.00', $expense->balance());

        $review($account, $entity, $expense->id(), 'revisado', '');
        self::assertTrue($expense->isDefinitive());
        self::assertSame('242.00', $expense->total());
        try {
            $review($account, $entity, $expense->id(), 'rechazado', 'Ya no');
            self::fail('Un gasto revisado no pasa a rechazado.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::STATE, $exception->reason);
        }
        try {
            $review($account, $entity, $expense->id(), 'anulado', '   ');
            self::fail('Anular exige un motivo.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::REVIEW_REASON, $exception->reason);
        }
        self::assertTrue($expense->isDefinitive());
        $review($account, $entity, $expense->id(), 'anulado', '  Factura   duplicada ');
        self::assertSame('anulado', $expense->reviewState());
        self::assertSame('Factura duplicada', $expense->reviewReason());
        self::assertSame('100.00', $expense->paid());
        self::assertFalse($expense->isCurrent());
        try {
            $pay($account, $entity, $expense->id(), '10,00', new \DateTimeImmutable('2026-11-02'), 'efectivo', '', Uuid::v7()->toRfc4122());
            self::fail('Un gasto anulado no admite otro pago.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FROZEN, $exception->reason);
        }
        try {
            $decide($account, $entity, $expense->id(), 'base', true, 'Papel');
            self::fail('Un gasto anulado no cambia la deducibilidad.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FROZEN, $exception->reason);
        }
        self::assertNull($expense->baseDeductible());

        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        try {
            $pay($other, $entity, $expense->id(), '10,00', new \DateTimeImmutable('2026-11-02'), 'efectivo', '', Uuid::v7()->toRfc4122());
            self::fail('Otra cuenta no anota el pago.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FORBIDDEN, $exception->reason);
        }
        self::assertSame('100.00', $expense->paid());

        $rejected = $register($account, $entity, $supplier->id(), $material->id(), 'ticket', 'efectivo', 'G-4', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Sobres', '1', '40,00', '0', '0'),
        ]);
        $pay($account, $entity, $rejected->id(), '10,00', new \DateTimeImmutable('2026-11-02'), 'efectivo', '', Uuid::v7()->toRfc4122());
        $review($account, $entity, $rejected->id(), 'rechazado', 'No es de la actividad');
        self::assertSame('rechazado', $rejected->reviewState());
        self::assertSame('parcial', $rejected->paymentStatus());
        self::assertSame('10.00', $rejected->paid());
        self::assertSame('40.00', $rejected->total());
        try {
            $review($account, $entity, $rejected->id(), 'revisado', '');
            self::fail('Un gasto rechazado no pasa a revisado.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::STATE, $exception->reason);
        }
    }

    public function testElDuplicadoSeAvisaYLaPropuestaNaceEnBorrador(): void
    {
        self::assertSame('2026-02-28', NextMonth::of(new \DateTimeImmutable('2026-01-31'))->format('Y-m-d'));
        self::assertSame('2024-02-29', NextMonth::of(new \DateTimeImmutable('2024-01-31'))->format('Y-m-d'));
        self::assertSame('G-1-P', ProposalNumber::from('G-1'));
        self::assertSame(40, mb_strlen(ProposalNumber::from(str_repeat('A', 40))));

        [$account, $entity, $ensure, $register, $mark, $propose, $pay, $deactivate, $categories, $contacts, $expenses] = $this->duplicates();
        ($ensure)($entity);
        $supplier = Contact::open($entity, 'Papelería Norte', 'B12345674', false, true, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($supplier);
        $otherSupplier = Contact::open($entity, 'Tinta Sur', '12345678Z', false, true, new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $contacts->save($otherSupplier);
        $material = $this->named($categories->allForEntity($entity), 'Material');
        $first = $register($account, $entity, $supplier->id(), $material->id(), 'factura', 'tarjeta', 'G-1', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Folios', '2', '100,00', '21', '0'),
        ]);
        $second = $register($account, $entity, $supplier->id(), $material->id(), 'ticket', 'efectivo', 'g-1', new \DateTimeImmutable('2026-11-03'), [
            $this->row('Clips', '1', '10', '0', '0'),
        ]);
        $stored = $expenses->forEntity($entity);
        $matches = ExpenseDuplicates::matching($second, $stored);
        self::assertCount(1, $matches);
        self::assertSame($first->id(), $matches[0]->id());
        self::assertSame('242.00', $first->total());
        self::assertSame('10.00', $second->total());

        $third = $register($account, $entity, $supplier->id(), $material->id(), 'factura', 'tarjeta', 'G-9', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Folios', '2', '100,00', '21', '0'),
        ]);
        self::assertSame($first->id(), ExpenseDuplicates::matching($third, $expenses->forEntity($entity))[0]->id());

        $foreign = $register($account, $entity, $otherSupplier->id(), $material->id(), 'factura', 'tarjeta', 'G-1', new \DateTimeImmutable('2026-11-02'), [
            $this->row('Folios', '2', '100,00', '21', '0'),
        ]);
        self::assertSame([], ExpenseDuplicates::matching($foreign, $expenses->forEntity($entity)));

        $pay($account, $entity, $first->id(), '50,00', new \DateTimeImmutable('2026-11-02'), 'transferencia', 'P-1', Uuid::v7()->toRfc4122());
        ($deactivate)($account, $entity, $material->id());
        $mark($account, $entity, $first->id(), true);
        self::assertTrue($first->isMonthly());
        $proposal = $propose($account, $entity, $first->id());
        self::assertSame('borrador', $proposal->reviewState());
        self::assertFalse($proposal->isDefinitive());
        self::assertNull($proposal->baseDeductible());
        self::assertSame('0.00', $proposal->paid());
        self::assertSame('242.00', $proposal->total());
        self::assertSame('200.00', $proposal->base());
        self::assertSame('42.00', $proposal->tax());
        self::assertSame('2026-12-02', $proposal->issuedOn()->format('Y-m-d'));
        self::assertSame('G-1-P', $proposal->documentNo());
        $origin = $proposal->sourceExpense();
        self::assertNotNull($origin);
        self::assertSame($first->id(), $origin->id());
        self::assertSame('50.00', $first->paid());
        self::assertSame('242.00', $first->total());
        try {
            $propose($account, $entity, $first->id());
            self::fail('La misma propuesta no se crea dos veces.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::PROPOSAL, $exception->reason);
        }
        $linked = 0;
        foreach ($expenses->forEntity($entity) as $expense) {
            $source = $expense->sourceExpense();
            if (null !== $source && $source->id() === $first->id()) {
                ++$linked;
            }
        }
        self::assertSame(1, $linked);

        $second->review('rechazado', 'No es de la actividad');
        try {
            $mark($account, $entity, $second->id(), true);
            self::fail('Un gasto rechazado no se marca como mensual.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FROZEN, $exception->reason);
        }

        $other = Account::register('otra@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        try {
            $propose($other, $entity, $first->id());
            self::fail('Otra cuenta no crea la propuesta.');
        } catch (InvalidExpense $exception) {
            self::assertSame(InvalidExpense::FORBIDDEN, $exception->reason);
        }
        $linked = 0;
        foreach ($expenses->forEntity($entity) as $expense) {
            $source = $expense->sourceExpense();
            if (null !== $source && $source->id() === $first->id()) {
                ++$linked;
            }
        }
        self::assertSame(1, $linked);
    }

    /**
     * @return array{0: Account, 1: FiscalEntity, 2: EnsureExpenseCategories, 3: SaveExpenseCategory, 4: DeactivateExpenseCategory, 5: RegisterExpense, 6: InMemoryExpenseCategoryRepository, 7: InMemoryContactRepository}
     */
    private function services(): array
    {
        $categories = new InMemoryExpenseCategoryRepository();
        $expenses = new InMemoryExpenseRepository();
        $contacts = new InMemoryContactRepository();
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $store = new InMemoryRegistrationStore();
        $clock = new MockClock('2026-11-02T10:00:00Z');
        $access = new ExpenseAccess($memberships);
        $account = Account::register('titular@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $entity = FiscalEntity::open('Taller Norte', 'B12345674', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $memberships->save(FiscalEntityMembership::hold($entity, $account));

        return [
            $account,
            $entity,
            new EnsureExpenseCategories($categories, $store, $clock),
            new SaveExpenseCategory($access, $categories, $store, $clock),
            new DeactivateExpenseCategory($access, $categories, $store),
            new RegisterExpense($access, $contacts, $categories, $expenses, $store, $clock),
            $categories,
            $contacts,
        ];
    }

    /**
     * @param list<ExpenseCategory> $categories
     */
    private function named(array $categories, string $name): ExpenseCategory
    {
        foreach ($categories as $category) {
            if ($category->name() === $name) {
                return $category;
            }
        }
        self::fail('Falta la categoría '.$name);
    }

    /**
     * @return array{0: Account, 1: FiscalEntity, 2: EnsureExpenseCategories, 3: RegisterExpense, 4: DecideExpenseDeduction, 5: InMemoryExpenseCategoryRepository, 6: InMemoryContactRepository}
     */
    private function deduction(): array
    {
        $categories = new InMemoryExpenseCategoryRepository();
        $expenses = new InMemoryExpenseRepository();
        $contacts = new InMemoryContactRepository();
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $store = new InMemoryRegistrationStore();
        $clock = new MockClock('2026-11-02T10:00:00Z');
        $access = new ExpenseAccess($memberships);
        $account = Account::register('titular@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $entity = FiscalEntity::open('Taller Norte', 'B12345674', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $memberships->save(FiscalEntityMembership::hold($entity, $account));

        return [
            $account,
            $entity,
            new EnsureExpenseCategories($categories, $store, $clock),
            new RegisterExpense($access, $contacts, $categories, $expenses, $store, $clock),
            new DecideExpenseDeduction($access, $expenses, $store),
            $categories,
            $contacts,
        ];
    }

    /**
     * @return array{0: Account, 1: FiscalEntity, 2: EnsureExpenseCategories, 3: RegisterExpense, 4: DecideExpenseDeduction, 5: PayExpense, 6: ReviewExpense, 7: InMemoryExpenseCategoryRepository, 8: InMemoryContactRepository}
     */
    private function revision(): array
    {
        [$account, $entity, $ensure, $register, $decide, $categories, $contacts] = $this->deduction();
        $expenses = new InMemoryExpenseRepository();
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $store = new InMemoryRegistrationStore();
        $clock = new MockClock('2026-11-02T10:00:00Z');
        $access = new ExpenseAccess($memberships);
        $memberships->save(FiscalEntityMembership::hold($entity, $account));

        return [
            $account,
            $entity,
            $ensure,
            new RegisterExpense($access, $contacts, $categories, $expenses, $store, $clock),
            new DecideExpenseDeduction($access, $expenses, $store),
            new PayExpense($access, $expenses, $store),
            new ReviewExpense($access, $expenses, $store),
            $categories,
            $contacts,
        ];
    }

    /**
     * @return array{0: Account, 1: FiscalEntity, 2: EnsureExpenseCategories, 3: RegisterExpense, 4: MarkExpenseRecurrence, 5: ProposeExpense, 6: PayExpense, 7: DeactivateExpenseCategory, 8: InMemoryExpenseCategoryRepository, 9: InMemoryContactRepository, 10: InMemoryExpenseRepository}
     */
    private function duplicates(): array
    {
        $categories = new InMemoryExpenseCategoryRepository();
        $expenses = new InMemoryExpenseRepository();
        $contacts = new InMemoryContactRepository();
        $memberships = new InMemoryFiscalEntityMembershipRepository();
        $store = new InMemoryRegistrationStore();
        $clock = new MockClock('2026-11-02T10:00:00Z');
        $access = new ExpenseAccess($memberships);
        $account = Account::register('titular@example.com', 'hash', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $entity = FiscalEntity::open('Taller Norte', 'B12345674', new \DateTimeImmutable('2026-11-02T10:00:00Z'));
        $memberships->save(FiscalEntityMembership::hold($entity, $account));

        return [
            $account,
            $entity,
            new EnsureExpenseCategories($categories, $store, $clock),
            new RegisterExpense($access, $contacts, $categories, $expenses, $store, $clock),
            new MarkExpenseRecurrence($access, $expenses, $store),
            new ProposeExpense($access, $expenses, $store, $clock),
            new PayExpense($access, $expenses, $store),
            new DeactivateExpenseCategory($access, $categories, $store),
            $categories,
            $contacts,
            $expenses,
        ];
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
