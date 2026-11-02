<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\Contact;
use App\Entity\FiscalEntity;
use App\Identity\RegistrationStore;
use Symfony\Component\Clock\ClockInterface;

final class SaveContact
{
    public function __construct(
        private readonly IncomeAccess $access,
        private readonly ContactRepository $contacts,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function create(Account $account, FiscalEntity $fiscalEntity, string $name, string $taxId, bool $client, bool $supplier): Contact
    {
        $this->access->holder($account, $fiscalEntity);
        $this->assertRoles($client, $supplier);
        $contact = Contact::open(
            $fiscalEntity,
            ContactName::canonical($name),
            $this->taxId($fiscalEntity, $taxId, null),
            $client,
            $supplier,
            $this->clock->now(),
        );
        $this->store->commit(function () use ($contact): void {
            $this->contacts->save($contact);
        });

        return $contact;
    }

    public function update(Account $account, FiscalEntity $fiscalEntity, string $contactId, string $name, string $taxId, bool $client, bool $supplier): Contact
    {
        $this->access->holder($account, $fiscalEntity);
        $contact = $this->contacts->findForEntity($fiscalEntity, $contactId);
        if (null === $contact) {
            throw new InvalidIncome(InvalidIncome::FORBIDDEN);
        }
        $this->assertRoles($client, $supplier);
        $contact->rename(
            ContactName::canonical($name),
            $this->taxId($fiscalEntity, $taxId, $contact->id()),
            $client,
            $supplier,
        );
        $this->store->commit(function () use ($contact): void {
            $this->contacts->save($contact);
        });

        return $contact;
    }

    private function assertRoles(bool $client, bool $supplier): void
    {
        if (!$client && !$supplier) {
            throw new InvalidIncome(InvalidIncome::ROLE);
        }
    }

    private function taxId(FiscalEntity $fiscalEntity, string $taxId, ?string $exceptId): string
    {
        $canonical = SpanishTaxId::canonical($taxId);
        $existing = $this->contacts->findByTaxId($fiscalEntity, $canonical);
        if (null !== $existing && $existing->id() !== $exceptId) {
            throw new InvalidIncome(InvalidIncome::EXISTS);
        }

        return $canonical;
    }
}
