<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryInvitation;
use App\Entity\AdvisoryMembership;
use Symfony\Component\Clock\ClockInterface;

final class AcceptAdvisoryInvitation
{
    public function __construct(
        private readonly AdvisoryInvitationRepository $invitations,
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function preview(Account $account, string $token): AdvisoryInvitation
    {
        return $this->usable($account, $token);
    }

    public function __invoke(Account $account, string $token): AdvisoryMembership
    {
        $invitation = $this->usable($account, $token);
        $existing = $this->memberships->findForFirmAndEmail($invitation->advisoryFirm(), $account->email());
        $now = $this->clock->now();
        $membership = $existing ?? AdvisoryMembership::employ($invitation->advisoryFirm(), $account);
        $this->store->commit(function () use ($invitation, $membership, $existing, $now): void {
            if (null === $existing) {
                $this->memberships->save($membership);
            }
            $invitation->use($now);
            $this->invitations->save($invitation);
        });

        return $membership;
    }

    private function usable(Account $account, string $token): AdvisoryInvitation
    {
        $invitation = $this->invitations->findByTokenHash(hash('sha256', $token));
        if (null === $invitation || !$invitation->isUsableAt($this->clock->now())) {
            throw new InvalidAdvisory(InvalidAdvisory::UNUSABLE);
        }
        if ($invitation->email() !== $account->email()) {
            throw new InvalidAdvisory(InvalidAdvisory::WRONG_ACCOUNT);
        }

        return $invitation;
    }
}
