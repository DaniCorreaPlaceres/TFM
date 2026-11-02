<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class ImportIncomes
{
    public function __construct(
        private readonly IncomeAccess $access,
        private readonly ContactRepository $contacts,
        private readonly IncomeRepository $incomes,
        private readonly IncomeActionRepository $actions,
        private readonly RegistrationStore $store,
        private readonly IncomeCsv $csv,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, string $contents): ImportReport
    {
        $this->access->holder($account, $fiscalEntity);
        $table = $this->csv->read($contents);
        if ($table->blocked) {
            return new ImportReport(0, $table->errors);
        }

        $groups = [];
        foreach ($table->rows as $row) {
            $groups[$this->key($row)][] = $row;
        }
        $imported = 0;
        $errors = [];
        foreach ($groups as $group) {
            $rowErrors = [];
            foreach ($group as $row) {
                $message = $this->rowError($row);
                if (null !== $message) {
                    $rowErrors[] = ['row' => $row['row'], 'message' => $message];
                }
            }
            if ([] !== $rowErrors) {
                $failed = [];
                foreach ($rowErrors as $rowError) {
                    $failed[$rowError['row']] = true;
                    $errors[] = $rowError;
                }
                foreach ($group as $row) {
                    if (!isset($failed[$row['row']])) {
                        $errors[] = ['row' => $row['row'], 'message' => 'No se ha importado porque otra fila del mismo ingreso no es válida.'];
                    }
                }
                continue;
            }
            try {
                $this->importGroup($account, $fiscalEntity, $group);
                ++$imported;
            } catch (InvalidIncome $exception) {
                $message = IncomeMessages::of($exception->reason);
                foreach ($group as $row) {
                    $errors[] = ['row' => $row['row'], 'message' => $message];
                }
            }
        }
        usort($errors, static fn (array $left, array $right): int => $left['row'] <=> $right['row']);

        return new ImportReport($imported, $errors);
    }

    /**
     * @param array{row: int, fecha: string, numero: string, contacto: string, nif: string, descripcion: string, cantidad: string, precio: string, iva: string, retencion: string} $row
     */
    private function key(array $row): string
    {
        if ('' === trim($row['numero'])) {
            return 'fila-'.$row['row'];
        }

        return trim($row['fecha']).'|'.trim($row['numero']).'|'.strtoupper(str_replace([' ', '-'], '', trim($row['nif'])));
    }

    /**
     * @param array{row: int, fecha: string, numero: string, contacto: string, nif: string, descripcion: string, cantidad: string, precio: string, iva: string, retencion: string} $row
     */
    private function rowError(array $row): ?string
    {
        if (null === CivilDate::parse($row['fecha'])) {
            return 'La fecha no es válida.';
        }
        try {
            SpanishTaxId::canonical($row['nif']);
        } catch (InvalidIncome) {
            return 'El NIF no es válido.';
        }
        try {
            ContactName::canonical($row['contacto']);
        } catch (InvalidIncome) {
            return 'El nombre del contacto no es válido.';
        }
        try {
            InvoiceNumber::canonical($row['numero']);
        } catch (InvalidIncome) {
            return 'El número no es válido.';
        }
        if ('' === trim($row['descripcion'])) {
            return 'La descripción es obligatoria.';
        }
        try {
            Amount::line($row['cantidad'], $row['precio'], $row['iva'], $row['retencion']);
        } catch (InvalidIncome) {
            return 'La cantidad, el precio o los porcentajes no son válidos.';
        }

        return null;
    }

    /**
     * @param list<array{row: int, fecha: string, numero: string, contacto: string, nif: string, descripcion: string, cantidad: string, precio: string, iva: string, retencion: string}> $group
     */
    private function importGroup(Account $account, FiscalEntity $fiscalEntity, array $group): void
    {
        $first = $group[0];
        $issuedOn = CivilDate::parse($first['fecha']);
        if (null === $issuedOn) {
            throw new InvalidIncome(InvalidIncome::DATE);
        }
        $this->access->open($fiscalEntity, $issuedOn);
        $taxId = SpanishTaxId::canonical($first['nif']);
        $invoiceNo = InvoiceNumber::canonical($first['numero']);
        if (null !== $this->incomes->findDuplicate($fiscalEntity, $invoiceNo, $taxId, $issuedOn, null)) {
            throw new InvalidIncome(InvalidIncome::DUPLICATE);
        }
        $contact = $this->contacts->findByTaxId($fiscalEntity, $taxId);
        $created = null;
        if (null === $contact) {
            $created = Contact::open($fiscalEntity, ContactName::canonical($first['contacto']), $taxId, true, false, $this->clock->now());
            $contact = $created;
        } elseif (!$contact->isClient()) {
            $contact->grantClient();
        }
        $rows = [];
        foreach ($group as $row) {
            $rows[] = [
                'description' => $row['descripcion'],
                'quantity' => $row['cantidad'],
                'unitPrice' => $row['precio'],
                'taxRate' => $row['iva'],
                'withholdingRate' => $row['retencion'],
            ];
        }
        $income = Income::register($fiscalEntity, $contact, $invoiceNo, $issuedOn, null, IncomeLines::parse($rows), $this->clock->now());
        $action = IncomeAction::record(
            $income,
            $account,
            'creado',
            'Ingreso importado. Total '.Euro::format($income->total()).'.',
            $this->clock->now(),
        );
        $this->store->commit(function () use ($created, $contact, $income, $action): void {
            if ($created instanceof Contact) {
                $this->contacts->save($created);
            } else {
                $this->contacts->save($contact);
            }
            $this->incomes->save($income);
            $this->actions->save($action);
        });
    }
}
