<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntity;
use App\Entity\FiscalEntityMembership;
use Symfony\Component\Clock\ClockInterface;

final class CreateFiscalEntity
{
    public function __construct(
        private readonly FiscalEntityRepository $entities,
        private readonly FiscalEntityMembershipRepository $memberships,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, string $legalName, string $taxId): FiscalEntity
    {
        $legalName = trim($legalName);
        $taxId = strtoupper(str_replace([' ', '-'], '', trim($taxId)));
        if ('' === $legalName || mb_strlen($legalName) > 180) {
            throw new InvalidFiscalEntity(InvalidFiscalEntity::NAME);
        }
        if ('' === $taxId || strlen($taxId) > 20 || 1 !== preg_match('/^[A-Z0-9]+$/', $taxId)) {
            throw new InvalidFiscalEntity(InvalidFiscalEntity::TAX_ID);
        }

        $fiscalEntity = FiscalEntity::open($legalName, $taxId, $this->clock->now());
        $membership = FiscalEntityMembership::hold($fiscalEntity, $account);
        $this->store->commit(function () use ($fiscalEntity, $membership): void {
            $this->entities->save($fiscalEntity);
            $this->memberships->save($membership);
        });

        return $fiscalEntity;
    }
}
