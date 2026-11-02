<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Contact;
use App\Entity\FiscalEntity;

interface ContactRepository
{
    public function save(Contact $contact): void;

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Contact;

    public function findByTaxId(FiscalEntity $fiscalEntity, string $taxId): ?Contact;

    /**
     * @return list<Contact>
     */
    public function search(FiscalEntity $fiscalEntity, string $query): array;
}
