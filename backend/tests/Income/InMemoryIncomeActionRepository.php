<?php

declare(strict_types=1);

namespace App\Tests\Income;

use App\Entity\Income;
use App\Entity\IncomeAction;
use App\Income\IncomeActionRepository;

final class InMemoryIncomeActionRepository implements IncomeActionRepository
{
    /** @var list<IncomeAction> */
    private array $actions = [];

    public function save(IncomeAction $action): void
    {
        $this->actions[] = $action;
    }

    public function forIncome(Income $income): array
    {
        $found = [];
        foreach ($this->actions as $action) {
            if ($action->income()->id() === $income->id()) {
                $found[] = $action;
            }
        }

        return $found;
    }
}
