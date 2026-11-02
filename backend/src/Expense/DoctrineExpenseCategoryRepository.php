<?php

declare(strict_types=1);

namespace App\Expense;

use App\Entity\ExpenseCategory;
use App\Entity\FiscalEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExpenseCategory>
 */
final class DoctrineExpenseCategoryRepository extends ServiceEntityRepository implements ExpenseCategoryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ExpenseCategory::class);
    }

    public function save(ExpenseCategory $category): void
    {
        $this->getEntityManager()->persist($category);
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?ExpenseCategory
    {
        $category = $this->find($id);

        return $category instanceof ExpenseCategory && $category->belongsTo($fiscalEntity) ? $category : null;
    }

    public function allForEntity(FiscalEntity $fiscalEntity): array
    {
        return $this->onlyCategories($this->createQueryBuilder('category')
            ->andWhere('category.fiscalEntity = :entity')
            ->setParameter('entity', $fiscalEntity)
            ->orderBy('category.name', 'ASC')
            ->getQuery()
            ->getResult());
    }

    /**
     * @return list<ExpenseCategory>
     */
    private function onlyCategories(mixed $rows): array
    {
        $categories = [];
        if (!is_array($rows)) {
            return $categories;
        }
        foreach ($rows as $row) {
            if ($row instanceof ExpenseCategory) {
                $categories[] = $row;
            }
        }

        return $categories;
    }
}
