<?php

declare(strict_types=1);

namespace App\Tests\Expense;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Expense\DeactivateExpenseCategory;
use App\Expense\DefaultExpenseCategories;
use App\Expense\EnsureExpenseCategories;
use App\Expense\ExpenseAccess;
use App\Expense\InvalidExpense;
use App\Expense\RegisterExpense;
use App\Expense\SaveExpenseCategory;
use App\Tests\Identity\InMemoryFiscalEntityMembershipRepository;
use App\Tests\Identity\InMemoryRegistrationStore;
use App\Tests\Income\InMemoryContactRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

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
