<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAttachment;

interface IncomeAttachmentRepository
{
    public function save(IncomeAttachment $attachment): void;

    public function findForIncome(FiscalEntity $fiscalEntity, Income $income, string $id): ?IncomeAttachment;

    /**
     * @return list<IncomeAttachment>
     */
    public function forIncome(Income $income): array;
}
