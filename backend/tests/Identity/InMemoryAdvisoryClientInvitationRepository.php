<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use App\Identity\AdvisoryClientInvitationRepository;

final class InMemoryAdvisoryClientInvitationRepository implements AdvisoryClientInvitationRepository
{
    /** @var list<AdvisoryClientInvitation> */
    private array $invitations = [];

    public function save(AdvisoryClientInvitation $invitation): void
    {
        $this->invitations[] = $invitation;
    }

    public function findByTokenHash(string $tokenHash): ?AdvisoryClientInvitation
    {
        foreach ($this->invitations as $invitation) {
            if ($invitation->tokenHash() === $tokenHash) {
                return $invitation;
            }
        }

        return null;
    }

    public function findByEntityAndEmail(FiscalEntity $fiscalEntity, string $email): array
    {
        return array_values(array_filter(
            $this->invitations,
            static fn (AdvisoryClientInvitation $invitation): bool => AdvisoryClientInvitation::FROM_ENTITY === $invitation->origin()
                && $invitation->fiscalEntity()?->id() === $fiscalEntity->id()
                && $invitation->email() === $email,
        ));
    }

    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array
    {
        return array_values(array_filter(
            $this->invitations,
            static fn (AdvisoryClientInvitation $invitation): bool => AdvisoryClientInvitation::FROM_FIRM === $invitation->origin()
                && $invitation->advisoryFirm()?->id() === $advisoryFirm->id()
                && $invitation->email() === $email,
        ));
    }
}
