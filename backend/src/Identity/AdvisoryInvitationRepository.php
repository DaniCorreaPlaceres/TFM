<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryInvitation;

interface AdvisoryInvitationRepository
{
    public function save(AdvisoryInvitation $invitation): void;

    public function findByTokenHash(string $tokenHash): ?AdvisoryInvitation;

    /**
     * @return list<AdvisoryInvitation>
     */
    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array;

    /**
     * @return list<AdvisoryInvitation>
     */
    public function findByEmail(string $email): array;
}
