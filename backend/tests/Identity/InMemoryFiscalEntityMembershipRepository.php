<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntityMembership;
use App\Identity\FiscalEntityMembershipRepository;

final class InMemoryFiscalEntityMembershipRepository implements FiscalEntityMembershipRepository
{
    /** @var list<FiscalEntityMembership> */
    private array $memberships = [];

    public function save(FiscalEntityMembership $membership): void
    {
        $this->memberships[] = $membership;
    }

    public function findForAccountAndEntity(Account $account, string $fiscalEntityId): ?FiscalEntityMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->belongsTo($account) && $membership->fiscalEntity()->id() === $fiscalEntityId) {
                return $membership;
            }
        }

        return null;
    }

    public function findForAccount(Account $account): array
    {
        $found = [];
        foreach ($this->memberships as $membership) {
            if ($membership->belongsTo($account)) {
                $found[] = $membership;
            }
        }

        return $found;
    }
}
