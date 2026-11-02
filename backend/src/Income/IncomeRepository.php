<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;

interface IncomeRepository
{
    public function save(Income $income): void;

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Income;

    public function findDuplicate(
        FiscalEntity $fiscalEntity,
        string $invoiceNo,
        string $taxId,
        \DateTimeImmutable $issuedOn,
        ?string $exceptId,
    ): ?Income;

    /**
     * @return list<Income>
     */
    public function search(FiscalEntity $fiscalEntity, string $query): array;
}
