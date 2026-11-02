<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryInvitation;
use App\Entity\AdvisoryMembership;
use Symfony\Component\Clock\ClockInterface;

final class JoinPendingAdvisoryInvitations
{
    public function __construct(
        private readonly AdvisoryInvitationRepository $invitations,
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account): void
    {
        $now = $this->clock->now();
        $pending = [];
        foreach ($this->invitations->findByEmail($account->email()) as $invitation) {
            if ($invitation->isUsableAt($now)) {
                $pending[] = $invitation;
            }
        }
        if ([] === $pending) {
            return;
        }

        $this->store->commit(function () use ($account, $pending, $now): void {
            foreach ($pending as $invitation) {
                $this->employ($account, $invitation);
                $invitation->use($now);
                $this->invitations->save($invitation);
            }
        });
    }

    private function employ(Account $account, AdvisoryInvitation $invitation): void
    {
        if (null !== $this->memberships->findForFirmAndEmail($invitation->advisoryFirm(), $account->email())) {
            return;
        }

        $this->memberships->save(AdvisoryMembership::employ($invitation->advisoryFirm(), $account));
    }
}
