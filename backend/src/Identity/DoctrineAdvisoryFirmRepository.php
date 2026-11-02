<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\AdvisoryFirm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryFirm>
 */
final class DoctrineAdvisoryFirmRepository extends ServiceEntityRepository implements AdvisoryFirmRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryFirm::class);
    }

    public function findById(string $id): ?AdvisoryFirm
    {
        $advisoryFirm = $this->find($id);

        return $advisoryFirm instanceof AdvisoryFirm ? $advisoryFirm : null;
    }

    public function save(AdvisoryFirm $advisoryFirm): void
    {
        $this->getEntityManager()->persist($advisoryFirm);
    }
}
