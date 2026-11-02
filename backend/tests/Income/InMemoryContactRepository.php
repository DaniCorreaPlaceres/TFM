<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\Contact;
use App\Entity\FiscalEntity;
use App\Income\ContactRepository;

final class InMemoryContactRepository implements ContactRepository
{
    /** @var array<string, Contact> */
    private array $contacts = [];

    public function save(Contact $contact): void
    {
        $this->contacts[$contact->id()] = $contact;
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Contact
    {
        $contact = $this->contacts[$id] ?? null;
        if (null === $contact || !$contact->belongsTo($fiscalEntity)) {
            return null;
        }

        return $contact;
    }

    public function findByTaxId(FiscalEntity $fiscalEntity, string $taxId): ?Contact
    {
        foreach ($this->contacts as $contact) {
            if ($contact->belongsTo($fiscalEntity) && $contact->taxId() === $taxId) {
                return $contact;
            }
        }

        return null;
    }

    public function search(FiscalEntity $fiscalEntity, string $query): array
    {
        $found = [];
        foreach ($this->contacts as $contact) {
            if (!$contact->belongsTo($fiscalEntity)) {
                continue;
            }
            if ('' === trim($query) || str_contains(mb_strtolower($contact->displayName()), mb_strtolower($query)) || str_contains($contact->taxId(), strtoupper($query))) {
                $found[] = $contact;
            }
        }

        return $found;
    }
}
