<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use App\Identity\AdvisoryMembershipRepository;

final class InMemoryAdvisoryMembershipRepository implements AdvisoryMembershipRepository
{
    /** @var list<AdvisoryMembership> */
    private array $memberships = [];

    public function save(AdvisoryMembership $membership): void
    {
        $this->memberships[] = $membership;
    }

    public function findById(string $id): ?AdvisoryMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->id() === $id) {
                return $membership;
            }
        }

        return null;
    }

    public function findForAccountAndFirm(Account $account, string $advisoryFirmId): ?AdvisoryMembership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->belongsTo($account) && $membership->advisoryFirm()->id() === $advisoryFirmId) {
                return $membership;
            }
        }

        return null;
    }

    public function findForFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): ?AdvisoryMembership
    {
        foreach ($this->findForFirm($advisoryFirm) as $membership) {
            if ($membership->account()->email() === $email) {
                return $membership;
            }
        }

        return null;
    }

    public function findForAccount(Account $account): array
    {
        return array_values(array_filter(
            $this->memberships,
            static fn (AdvisoryMembership $membership): bool => $membership->belongsTo($account),
        ));
    }

    public function findForFirm(AdvisoryFirm $advisoryFirm): array
    {
        return array_values(array_filter(
            $this->memberships,
            static fn (AdvisoryMembership $membership): bool => $membership->advisoryFirm()->id() === $advisoryFirm->id(),
        ));
    }
}
