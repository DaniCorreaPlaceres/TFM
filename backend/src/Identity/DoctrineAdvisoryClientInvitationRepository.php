<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryClientInvitation;
use App\Entity\AdvisoryFirm;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryClientInvitation>
 */
final class DoctrineAdvisoryClientInvitationRepository extends ServiceEntityRepository implements AdvisoryClientInvitationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryClientInvitation::class);
    }

    public function save(AdvisoryClientInvitation $invitation): void
    {
        $this->getEntityManager()->persist($invitation);
    }

    public function findByTokenHash(string $tokenHash): ?AdvisoryClientInvitation
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function findByEntityAndEmail(FiscalEntity $fiscalEntity, string $email): array
    {
        return $this->findBy([
            'origin' => AdvisoryClientInvitation::FROM_ENTITY,
            'fiscalEntity' => $fiscalEntity,
            'email' => $email,
        ]);
    }

    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array
    {
        return $this->findBy([
            'origin' => AdvisoryClientInvitation::FROM_FIRM,
            'advisoryFirm' => $advisoryFirm,
            'email' => $email,
        ]);
    }
}
