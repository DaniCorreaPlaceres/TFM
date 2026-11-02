<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class CorrectIncome
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
        string $incomeId,
        int $version,
        string $contactId,
        string $invoiceNo,
        \DateTimeImmutable $issuedOn,
        ?\DateTimeImmutable $dueOn,
        array $rows,
    ): Income {
        $income = $this->access->income($account, $fiscalEntity, $incomeId);
        $this->access->version($income, $version);
        $this->access->open($fiscalEntity, $income->issuedOn());
        $contact = $this->contacts->findForEntity($fiscalEntity, $contactId);
        if (null === $contact || !$contact->isClient()) {
            throw new InvalidIncome(InvalidIncome::CONTACT);
        }
        $invoiceNo = InvoiceNumber::canonical($invoiceNo);
        if (null !== $dueOn && $dueOn < $issuedOn) {
            throw new InvalidIncome(InvalidIncome::DUE);
        }
        $this->access->open($fiscalEntity, $issuedOn);
        $lines = IncomeLines::parse($rows);
        $snapshotTaxId = $contact->id() === $income->contact()->id() ? $income->snapshotTaxId() : $contact->taxId();
        if (null !== $this->incomes->findDuplicate($fiscalEntity, $invoiceNo, $snapshotTaxId, $issuedOn, $income->id())) {
            throw new InvalidIncome(InvalidIncome::DUPLICATE);
        }
        $previous = $income->total();
        $income->correct($contact, $invoiceNo, $issuedOn, $dueOn, $lines);
        $action = IncomeAction::record(
            $income,
            $account,
            'corregido',
            'Ingreso corregido. Total anterior '.Euro::format($previous).', total nuevo '.Euro::format($income->total()).'.',
            $this->clock->now(),
        );
        $this->store->commit(function () use ($income, $action): void {
            $this->incomes->save($income);
            $this->actions->save($action);
        });

        return $income;
    }
}
