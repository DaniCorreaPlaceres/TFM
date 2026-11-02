<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\Expense;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use App\Income\ContactRepository;
use Symfony\Component\Clock\ClockInterface;

final class RegisterExpense
{
    public function __construct(
        private readonly ExpenseAccess $access,
        private readonly ContactRepository $contacts,
        private readonly ExpenseCategoryRepository $categories,
        private readonly ExpenseRepository $expenses,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    /**
     * @param list<array{description: string, quantity: string, unitPrice: string, taxRate: string, withholdingRate: string}> $rows
     */
    public function __invoke(
        Account $account,
        FiscalEntity $fiscalEntity,
        string $contactId,
        string $categoryId,
        string $kind,
        string $paymentMethod,
        string $documentNo,
        \DateTimeImmutable $issuedOn,
        array $rows,
    ): Expense {
        $this->access->holder($account, $fiscalEntity);
        $contact = $this->contacts->findForEntity($fiscalEntity, $contactId);
        if (null === $contact || !$contact->isSupplier()) {
            throw new InvalidExpense(InvalidExpense::CONTACT);
        }
        $category = $this->categories->findForEntity($fiscalEntity, $categoryId);
        if (null === $category || !$category->isActive()) {
            throw new InvalidExpense(InvalidExpense::CATEGORY);
        }
        $expense = Expense::register(
            $fiscalEntity,
            $contact,
            $category,
            ExpenseKind::canonical($kind),
            PaymentMethod::canonical($paymentMethod),
            DocumentNumber::canonical($documentNo),
            $issuedOn,
            ExpenseLines::parse($rows),
            $this->clock->now(),
        );
        $this->store->commit(function () use ($expense): void {
            $this->expenses->save($expense);
        });

        return $expense;
    }
}
