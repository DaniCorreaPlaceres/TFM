<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;

interface AdvisoryClientInvitationRepository
{
    public function save(AdvisoryClientInvitation $invitation): void;

    public function findByTokenHash(string $tokenHash): ?AdvisoryClientInvitation;

    /**
     * @return list<AdvisoryClientInvitation>
     */
    public function findByEntityAndEmail(FiscalEntity $fiscalEntity, string $email): array;

    /**
     * @return list<AdvisoryClientInvitation>
     */
    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array;
}
