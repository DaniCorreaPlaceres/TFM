<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Entity\Income;
use App\Identity\FiscalEntityMembershipRepository;

final class IncomeAccess
{
    public function __construct(
        private readonly FiscalEntityMembershipRepository $memberships,
        private readonly IncomeRepository $incomes,
        private readonly ClosedQuarterRepository $quarters,
    ) {
    }

    public function holder(Account $account, FiscalEntity $fiscalEntity): void
    {
        $membership = $this->memberships->findForAccountAndEntity($account, $fiscalEntity->id());
        if (null === $membership || FiscalEntityMembership::HOLDER !== $membership->role()) {
            throw new InvalidIncome(InvalidIncome::FORBIDDEN);
        }
    }

    public function income(Account $account, FiscalEntity $fiscalEntity, string $incomeId): Income
    {
        $this->holder($account, $fiscalEntity);
        $income = $this->incomes->findForEntity($fiscalEntity, $incomeId);
        if (null === $income) {
            throw new InvalidIncome(InvalidIncome::FORBIDDEN);
        }

        return $income;
    }

    public function open(FiscalEntity $fiscalEntity, \DateTimeImmutable $date): void
    {
        [$year, $quarter] = Quarter::of($date);
        if (null !== $this->quarters->findOne($fiscalEntity->id(), $year, $quarter)) {
            throw new InvalidIncome(InvalidIncome::CLOSED);
        }
    }

    public function version(Income $income, int $version): void
    {
        if ($income->version() !== $version) {
            throw new InvalidIncome(InvalidIncome::STALE);
        }
    }
}
