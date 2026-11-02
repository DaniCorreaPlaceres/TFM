<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryInvitation;
use Symfony\Component\Clock\ClockInterface;

final class InviteAdvisoryEmployee
{
    public function __construct(
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly AdvisoryInvitationRepository $invitations,
        private readonly RegistrationStore $store,
        private readonly AccountMailer $mailer,
        private readonly AdvisoryInvitationLink $links,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, string $advisoryFirmId, string $email): void
    {
        $email = mb_strtolower(trim($email));
        if (strlen($email) > 180 || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidAdvisory(InvalidAdvisory::EMAIL);
        }

        $membership = $this->memberships->findForAccountAndFirm($account, $advisoryFirmId);
        if (null === $membership || !$membership->isAdmin()) {
            throw new InvalidAdvisory(InvalidAdvisory::FORBIDDEN);
        }

        $advisoryFirm = $membership->advisoryFirm();
        if (null !== $this->memberships->findForFirmAndEmail($advisoryFirm, $email)) {
            throw new InvalidAdvisory(InvalidAdvisory::ALREADY_MEMBER);
        }

        $now = $this->clock->now();
        $token = bin2hex(random_bytes(32));
        $invitation = AdvisoryInvitation::issue(
            $advisoryFirm,
            $account,
            $email,
            hash('sha256', $token),
            $now->modify('+7 days'),
        );
        $this->store->commit(function () use ($advisoryFirm, $email, $invitation, $token, $now): void {
            foreach ($this->invitations->findByFirmAndEmail($advisoryFirm, $email) as $previous) {
                if ($previous->isUsableAt($now)) {
                    $previous->use($now);
                    $this->invitations->save($previous);
                }
            }
            $this->invitations->save($invitation);
            $this->mailer->sendAdvisoryInvitation($email, $advisoryFirm->name(), $this->links->url($token));
        });
    }
}
