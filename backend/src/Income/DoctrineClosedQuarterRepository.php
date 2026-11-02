<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\ClosedQuarter;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ClosedQuarter>
 */
final class DoctrineClosedQuarterRepository extends ServiceEntityRepository implements ClosedQuarterRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClosedQuarter::class);
    }

    public function save(ClosedQuarter $closedQuarter): void
    {
        $this->getEntityManager()->persist($closedQuarter);
    }

    public function findOne(string $fiscalEntityId, int $year, int $quarter): ?ClosedQuarter
    {
        $closed = $this->createQueryBuilder('q')
            ->join('q.fiscalEntity', 'entity')
            ->andWhere('entity.id = :entity')
            ->andWhere('q.year = :year')
            ->andWhere('q.quarter = :quarter')
            ->setParameter('entity', $fiscalEntityId)
            ->setParameter('year', $year)
            ->setParameter('quarter', $quarter)
            ->getQuery()
            ->getOneOrNullResult();

        return $closed instanceof ClosedQuarter ? $closed : null;
    }

    public function forEntity(FiscalEntity $fiscalEntity): array
    {
        $rows = $this->createQueryBuilder('q')
            ->andWhere('q.fiscalEntity = :entity')
            ->setParameter('entity', $fiscalEntity)
            ->orderBy('q.year', 'DESC')
            ->addOrderBy('q.quarter', 'DESC')
            ->getQuery()
            ->getResult();
        $quarters = [];
        if (!is_array($rows)) {
            return $quarters;
        }
        foreach ($rows as $row) {
            if ($row instanceof ClosedQuarter) {
                $quarters[] = $row;
            }
        }

        return $quarters;
    }
}
