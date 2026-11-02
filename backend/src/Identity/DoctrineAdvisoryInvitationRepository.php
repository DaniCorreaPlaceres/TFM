<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryFirm;
use App\Entity\AdvisoryInvitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryInvitation>
 */
final class DoctrineAdvisoryInvitationRepository extends ServiceEntityRepository implements AdvisoryInvitationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryInvitation::class);
    }

    public function save(AdvisoryInvitation $invitation): void
    {
        $this->getEntityManager()->persist($invitation);
    }

    public function findByTokenHash(string $tokenHash): ?AdvisoryInvitation
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function findByFirmAndEmail(AdvisoryFirm $advisoryFirm, string $email): array
    {
        return $this->findBy([
            'advisoryFirm' => $advisoryFirm,
            'email' => $email,
        ]);
    }

    public function findByEmail(string $email): array
    {
        return $this->findBy(['email' => $email]);
    }
}
