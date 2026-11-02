<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryClientLink;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use Symfony\Component\Clock\ClockInterface;

final class AcceptAdvisoryClientInvitation
{
    public function __construct(
        private readonly AdvisoryClientInvitationRepository $invitations,
        private readonly AdvisoryClientLinkRepository $links,
        private readonly AdvisoryMembershipRepository $advisoryMemberships,
        private readonly FiscalEntityMembershipRepository $fiscalMemberships,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function preview(Account $account, string $token): AdvisoryClientInvitation
    {
        return $this->usable($account, $token);
    }

    /**
     * @return list<AdvisoryFirm>
     */
    public function firms(Account $account): array
    {
        $firms = [];
        foreach ($this->advisoryMemberships->findForAccount($account) as $membership) {
            if ($membership->isAdmin()) {
                $firms[] = $membership->advisoryFirm();
            }
        }

        return $firms;
    }

    /**
     * @return list<FiscalEntity>
     */
    public function entities(Account $account): array
    {
        $entities = [];
        foreach ($this->fiscalMemberships->findForAccount($account) as $membership) {
            $entities[] = $membership->fiscalEntity();
        }

        return $entities;
    }

    public function __invoke(Account $account, string $token, string $targetId): AdvisoryClientLink
    {
        $invitation = $this->usable($account, $token);
        $now = $this->clock->now();
        if (AdvisoryClientInvitation::FROM_ENTITY === $invitation->origin()) {
            $fiscalEntity = $invitation->fiscalEntity();
            if (!$fiscalEntity instanceof FiscalEntity) {
                throw new InvalidAdvisory(InvalidAdvisory::UNUSABLE);
            }
            $advisoryFirm = $this->administeredFirm($account, $targetId);
            $link = $this->open($advisoryFirm, $fiscalEntity, $now);
        } else {
            $advisoryFirm = $invitation->advisoryFirm();
            if (!$advisoryFirm instanceof AdvisoryFirm) {
                throw new InvalidAdvisory(InvalidAdvisory::UNUSABLE);
            }
            $fiscalEntity = $this->heldEntity($account, $targetId);
            $link = $this->open($advisoryFirm, $fiscalEntity, $now);
        }

        $invitation->use($now);
        $this->store->commit(function () use ($link, $invitation): void {
            $this->links->save($link);
            $this->invitations->save($invitation);
        });

        return $link;
    }

    private function usable(Account $account, string $token): AdvisoryClientInvitation
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

    private function administeredFirm(Account $account, string $advisoryFirmId): AdvisoryFirm
    {
        foreach ($this->firms($account) as $advisoryFirm) {
            if ($advisoryFirm->id() === $advisoryFirmId) {
                return $advisoryFirm;
            }
        }

        throw new InvalidAdvisory(InvalidAdvisory::FORBIDDEN);
    }

    private function heldEntity(Account $account, string $fiscalEntityId): FiscalEntity
    {
        foreach ($this->entities($account) as $fiscalEntity) {
            if ($fiscalEntity->id() === $fiscalEntityId) {
                return $fiscalEntity;
            }
        }

        throw new InvalidAdvisory(InvalidAdvisory::FORBIDDEN);
    }

    private function open(AdvisoryFirm $advisoryFirm, FiscalEntity $fiscalEntity, \DateTimeImmutable $now): AdvisoryClientLink
    {
        if (null !== $this->links->findByFiscalEntity($fiscalEntity)) {
            throw new InvalidAdvisory(InvalidAdvisory::ALREADY_LINKED);
        }

        return AdvisoryClientLink::open($advisoryFirm, $fiscalEntity, $now);
    }
}
