<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Income>
 */
final class DoctrineIncomeRepository extends ServiceEntityRepository implements IncomeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Income::class);
    }

    public function save(Income $income): void
    {
        $this->getEntityManager()->persist($income);
    }

    public function findForEntity(FiscalEntity $fiscalEntity, string $id): ?Income
    {
        $income = $this->createQueryBuilder('i')
            ->andWhere('i.id = :id')
            ->andWhere('i.fiscalEntity = :entity')
            ->setParameter('id', $id)
            ->setParameter('entity', $fiscalEntity)
            ->getQuery()
            ->getOneOrNullResult();

        return $income instanceof Income ? $income : null;
    }

    public function findDuplicate(
        FiscalEntity $fiscalEntity,
        string $invoiceNo,
        string $taxId,
        \DateTimeImmutable $issuedOn,
        ?string $exceptId,
    ): ?Income {
        if ('' === $invoiceNo) {
            return null;
        }
        $builder = $this->createQueryBuilder('i')
            ->andWhere('i.fiscalEntity = :entity')
            ->andWhere('i.invoiceNo = :invoice')
            ->andWhere('i.snapshotTaxId = :taxId')
            ->andWhere('i.issuedOn = :issued')
            ->setParameter('entity', $fiscalEntity)
            ->setParameter('invoice', $invoiceNo)
            ->setParameter('taxId', $taxId)
            ->setParameter('issued', $issuedOn)
            ->setMaxResults(1);
        if (null !== $exceptId) {
            $builder->andWhere('i.id != :except')->setParameter('except', $exceptId);
        }
        $income = $builder->getQuery()->getOneOrNullResult();

        return $income instanceof Income ? $income : null;
    }

    public function search(FiscalEntity $fiscalEntity, string $query): array
    {
        $builder = $this->createQueryBuilder('i')
            ->andWhere('i.fiscalEntity = :entity')
            ->setParameter('entity', $fiscalEntity)
            ->orderBy('i.issuedOn', 'DESC')
            ->addOrderBy('i.invoiceNo', 'ASC');
        if ('' !== trim($query)) {
            $builder->andWhere('i.invoiceNo LIKE :query OR i.snapshotName LIKE :query OR i.snapshotTaxId LIKE :query')
                ->setParameter('query', SearchTerm::like($query));
        }
        $rows = $builder->getQuery()->getResult();
        $incomes = [];
        if (!is_array($rows)) {
            return $incomes;
        }
        foreach ($rows as $row) {
            if ($row instanceof Income) {
                $incomes[] = $row;
            }
        }

        return $incomes;
    }
}
