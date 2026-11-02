<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\Expense;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expense>
 */
final class DoctrineExpenseRepository extends ServiceEntityRepository implements ExpenseRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expense::class);
    }

    public function save(Expense $expense): void
    {
        $this->getEntityManager()->persist($expense);
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Expense
    {
        $expense = $this->find($id);

        return $expense instanceof Expense && $expense->belongsTo($fiscalEntity) ? $expense : null;
    }

    public function forEntity(FiscalEntity $fiscalEntity): array
    {
        return $this->onlyExpenses($this->createQueryBuilder('expense')
            ->andWhere('expense.fiscalEntity = :entity')
            ->setParameter('entity', $fiscalEntity)
            ->orderBy('expense.issuedOn', 'DESC')
            ->addOrderBy('expense.documentNo', 'ASC')
            ->getQuery()
            ->getResult());
    }

    /**
     * @return list<Expense>
     */
    private function onlyExpenses(mixed $rows): array
    {
        $expenses = [];
        if (!is_array($rows)) {
            return $expenses;
        }
        foreach ($rows as $row) {
            if ($row instanceof Expense) {
                $expenses[] = $row;
            }
        }

        return $expenses;
    }
}
