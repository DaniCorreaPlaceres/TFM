<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryEmployeePermission;
use Symfony\Component\Clock\ClockInterface;

final class SetEmployeeEntityView
{
    public function __construct(
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly AdvisoryEmployeePermissionRepository $permissions,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, string $advisoryFirmId, string $membershipId, bool $granted): void
    {
        $actor = $this->memberships->findForAccountAndFirm($account, $advisoryFirmId);
        $target = $this->memberships->findById($membershipId);
        if (
            null === $actor
            || !$actor->isAdmin()
            || null === $target
            || $target->isAdmin()
            || $target->advisoryFirm()->id() !== $advisoryFirmId
        ) {
            throw new InvalidAdvisory(InvalidAdvisory::FORBIDDEN);
        }

        $existing = $this->permissions->findForMembership($target);
        $this->store->commit(function () use ($granted, $existing, $target): void {
            if ($granted && null === $existing) {
                $this->permissions->save(AdvisoryEmployeePermission::viewEntities($target, $this->clock->now()));
            }
            if (!$granted && null !== $existing) {
                $this->permissions->remove($existing);
            }
        });
    }
}
