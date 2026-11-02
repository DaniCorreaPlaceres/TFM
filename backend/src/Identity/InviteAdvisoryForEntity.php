<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryClientInvitation;
use App\Entity\FiscalEntity;
use Symfony\Component\Clock\ClockInterface;

final class InviteAdvisoryForEntity
{
    public function __construct(
        private readonly FiscalEntityMembershipRepository $fiscalMemberships,
        private readonly AdvisoryClientLinkRepository $links,
        private readonly AdvisoryClientInvitationRepository $invitations,
        private readonly RegistrationStore $store,
        private readonly AccountMailer $mailer,
        private readonly AdvisoryClientInvitationLink $invitationLinks,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, FiscalEntity $fiscalEntity, string $email): void
    {
        if (null === $this->fiscalMemberships->findForAccountAndEntity($account, $fiscalEntity->id())) {
            throw new InvalidAdvisory(InvalidAdvisory::FORBIDDEN);
        }

        $email = $this->email($email);
        if (null !== $this->links->findByFiscalEntity($fiscalEntity)) {
            throw new InvalidAdvisory(InvalidAdvisory::ALREADY_LINKED);
        }

        $now = $this->clock->now();
        $token = bin2hex(random_bytes(32));
        $invitation = AdvisoryClientInvitation::fromEntity(
            $fiscalEntity,
            $account,
            $email,
            hash('sha256', $token),
            $now->modify('+7 days'),
        );
        $this->store->commit(function () use ($fiscalEntity, $email, $invitation, $token, $now): void {
            foreach ($this->invitations->findByEntityAndEmail($fiscalEntity, $email) as $previous) {
                if ($previous->isUsableAt($now)) {
                    $previous->use($now);
                    $this->invitations->save($previous);
                }
            }
            $this->invitations->save($invitation);
            $this->mailer->sendAdvisoryClientInvitation($email, $fiscalEntity->legalName(), $this->invitationLinks->url($token));
        });
    }

    private function email(string $email): string
    {
        $email = mb_strtolower(trim($email));
        if (strlen($email) > 180 || false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidAdvisory(InvalidAdvisory::EMAIL);
        }

        return $email;
    }
}
