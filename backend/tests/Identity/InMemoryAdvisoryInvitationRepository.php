<?php

declare(strict_types=1);

namespace App\Tests\Identity;

use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryInvitation;
use App\Identity\AdvisoryInvitationRepository;

final class InMemoryAdvisoryInvitationRepository implements AdvisoryInvitationRepository
{
    /** @var list<AdvisoryInvitation> */
    private array $invitations = [];

    public function save(AdvisoryInvitation $invitation): void
    {
        $this->invitations[] = $invitation;
    }

    public function findByTokenHash(string $tokenHash): ?AdvisoryInvitation
    {
        foreach ($this->invitations as $invitation) {
            if ($invitation->tokenHash() === $tokenHash) {
                return $invitation;
            }
        }

        return null;
    }

    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array
    {
        return array_values(array_filter(
            $this->invitations,
            static fn (AdvisoryInvitation $invitation): bool => $invitation->advisoryFirm()->id() === $advisoryFirm->id() && $invitation->email() === $email,
        ));
    }

    public function findByEmail(string $email): array
    {
        return array_values(array_filter(
            $this->invitations,
            static fn (AdvisoryInvitation $invitation): bool => $invitation->email() === $email,
        ));
    }
}
