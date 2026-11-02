<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryMembership;

interface AdvisoryMembershipRepository
{
    public function save(AdvisoryMembership $membership): void;

    public function findForAccountAndFirm(Account $account, string $advisoryFirmId): ?AdvisoryMembership;

    public function findForFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): ?AdvisoryMembership;

    /**
     * @return list<AdvisoryMembership>
     */
    public function findForAccount(Account $account): array;

    /**
     * @return list<AdvisoryMembership>
     */
    public function findForFirm(AdvisoryFirm $advisoryFirm): array;
}
