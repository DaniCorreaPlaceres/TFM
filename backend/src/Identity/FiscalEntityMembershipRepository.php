<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntityMembership;

interface FiscalEntityMembershipRepository
{
    public function save(FiscalEntityMembership $membership): void;

    public function findForAccountAndEntity(Account $account, string $fiscalEntityId): ?FiscalEntityMembership;

    /**
     * @return list<FiscalEntityMembership>
     */
    public function findForAccount(Account $account): array;
}
