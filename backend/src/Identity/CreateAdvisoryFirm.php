<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;
use Symfony\Component\Clock\ClockInterface;

final class CreateAdvisoryFirm
{
    public function __construct(
        private readonly AdvisoryFirmRepository $firms,
        private readonly AdvisoryMembershipRepository $memberships,
        private readonly RegistrationStore $store,
        private readonly ClockInterface $clock,
    ) {
    }

    public function __invoke(Account $account, string $name, string $taxId): AdvisoryFirm
    {
        $name = trim($name);
        $taxId = strtoupper(str_replace([' ', '-'], '', trim($taxId)));
        if ('' === $name || mb_strlen($name) > 180) {
            throw new InvalidAdvisory(InvalidAdvisory::NAME);
        }
        if ('' === $taxId || strlen($taxId) > 20 || 1 !== preg_match('/^[A-Z0-9]+$/', $taxId)) {
            throw new InvalidAdvisory(InvalidAdvisory::TAX_ID);
        }

        $advisoryFirm = AdvisoryFirm::open($name, $taxId, $this->clock->now());
        $membership = AdvisoryMembership::administer($advisoryFirm, $account);
        $this->store->commit(function () use ($advisoryFirm, $membership): void {
            $this->firms->save($advisoryFirm);
            $this->memberships->save($membership);
        });

        return $advisoryFirm;
    }
}
