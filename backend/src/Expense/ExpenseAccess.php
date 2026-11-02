<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use App\Identity\FiscalEntityMembershipRepository;

final class ExpenseAccess
{
    public function __construct(private readonly FiscalEntityMembershipRepository $memberships)
    {
    }

    public function holder(Account $account, FiscalEntity $fiscalEntity): void
    {
        $membership = $this->memberships->findForAccountAndEntity($account, $fiscalEntity->id());
        if (null === $membership || FiscalEntityMembership::HOLDER !== $membership->role()) {
            throw new InvalidExpense(InvalidExpense::FORBIDDEN);
        }
    }
}
