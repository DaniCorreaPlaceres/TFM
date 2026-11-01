<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FiscalEntity>
 */
final class DoctrineFiscalEntityRepository extends ServiceEntityRepository implements FiscalEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiscalEntity::class);
    }

    public function findById(string $id): ?FiscalEntity
    {
        $fiscalEntity = $this->find($id);

        return $fiscalEntity instanceof FiscalEntity ? $fiscalEntity : null;
    }

    public function save(FiscalEntity $fiscalEntity): void
    {
        $this->getEntityManager()->persist($fiscalEntity);
    }
}
