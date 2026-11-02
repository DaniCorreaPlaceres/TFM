<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class RegisterIncome
{
    public function __construct(
        private readonly IncomeAccess $access,
        private readonly ContactRepository $contacts,
        private readonly IncomeRepository $incomes,
        private readonly IncomeActionRepository $actions,
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
        string $invoiceNo,
        \DateTimeImmutable $issuedOn,
        ?\DateTimeImmutable $dueOn,
        array $rows,
    ): Income {
        $this->access->holder($account, $fiscalEntity);
        $contact = $this->contacts->findForEntity($fiscalEntity, $contactId);
        if (null === $contact || !$contact->isClient()) {
            throw new InvalidIncome(InvalidIncome::CONTACT);
        }
        $invoiceNo = InvoiceNumber::canonical($invoiceNo);
        $this->assertDates($issuedOn, $dueOn);
        $this->access->open($fiscalEntity, $issuedOn);
        $lines = IncomeLines::parse($rows);
        if (null !== $this->incomes->findDuplicate($fiscalEntity, $invoiceNo, $contact->taxId(), $issuedOn, null)) {
            throw new InvalidIncome(InvalidIncome::DUPLICATE);
        }
        $income = Income::register($fiscalEntity, $contact, $invoiceNo, $issuedOn, $dueOn, $lines, $this->clock->now());
        $action = IncomeAction::record(
            $income,
            $account,
            'creado',
            'Ingreso registrado. Total '.Euro::format($income->total()).'.',
            $this->clock->now(),
        );
        $this->store->commit(function () use ($income, $action): void {
            $this->incomes->save($income);
            $this->actions->save($action);
        });

        return $income;
    }

    private function assertDates(\DateTimeImmutable $issuedOn, ?\DateTimeImmutable $dueOn): void
    {
        if (null !== $dueOn && $dueOn < $issuedOn) {
            throw new InvalidIncome(InvalidIncome::DUE);
        }
    }
}
