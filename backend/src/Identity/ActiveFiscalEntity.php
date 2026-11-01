<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use Symfony\Component\HttpFoundation\Request;

final class ActiveFiscalEntity
{
    public function __construct(private readonly FiscalEntityMembershipRepository $memberships)
    {
    }

    public function resolve(Account $account, Request $request): ?FiscalEntity
    {
        $selected = $request->getSession()->get('fiscal_entity_id');
        if (is_string($selected)) {
            $membership = $this->memberships->findForAccountAndEntity($account, $selected);
            if (null !== $membership) {
                return $membership->fiscalEntity();
            }
            $request->getSession()->remove('fiscal_entity_id');
        }

        $memberships = $this->memberships->findForAccount($account);
        if (1 === count($memberships)) {
            $fiscalEntity = $memberships[0]->fiscalEntity();
            $request->getSession()->set('fiscal_entity_id', $fiscalEntity->id());

            return $fiscalEntity;
        }

        return null;
    }

    public function select(Account $account, Request $request, string $fiscalEntityId): bool
    {
        $membership = $this->memberships->findForAccountAndEntity($account, $fiscalEntityId);
        if (null === $membership) {
            return false;
        }

        $request->getSession()->set('fiscal_entity_id', $membership->fiscalEntity()->id());

        return true;
    }
}
