<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\ClosedQuarter;
use App\Entity\FiscalEntity;

interface ClosedQuarterRepository
{
    public function save(ClosedQuarter $closedQuarter): void;

    public function findOne(string $fiscalEntityId, int $year, int $quarter): ?ClosedQuarter;

    /**
     * @return list<ClosedQuarter>
     */
    public function forEntity(FiscalEntity $fiscalEntity): array;
}
