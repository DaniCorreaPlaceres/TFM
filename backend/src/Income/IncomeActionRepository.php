<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Income;
use App\Entity\IncomeAction;

interface IncomeActionRepository
{
    public function save(IncomeAction $action): void;

    /**
     * @return list<IncomeAction>
     */
    public function forIncome(Income $income): array;
}
