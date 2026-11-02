<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\Income;
use App\Entity\IncomeAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IncomeAction>
 */
final class DoctrineIncomeActionRepository extends ServiceEntityRepository implements IncomeActionRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeAction::class);
    }

    public function save(IncomeAction $action): void
    {
        $this->getEntityManager()->persist($action);
    }

    public function forIncome(Income $income): array
    {
        $rows = $this->createQueryBuilder('a')
            ->andWhere('a.income = :income')
            ->andWhere('a.fiscalEntity = :entity')
            ->setParameter('income', $income)
            ->setParameter('entity', $income->fiscalEntity())
            ->orderBy('a.actedAt', 'ASC')
            ->getQuery()
            ->getResult();
        $actions = [];
        if (!is_array($rows)) {
            return $actions;
        }
        foreach ($rows as $row) {
            if ($row instanceof IncomeAction) {
                $actions[] = $row;
            }
        }

        return $actions;
    }
}
