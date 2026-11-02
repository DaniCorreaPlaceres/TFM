<?php

declare(strict_types=1);

namespace App\Income;

use App\Entity\FiscalEntity;
use App\Entity\Income;
use App\Entity\IncomeAttachment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IncomeAttachment>
 */
final class DoctrineIncomeAttachmentRepository extends ServiceEntityRepository implements IncomeAttachmentRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IncomeAttachment::class);
    }

    public function save(IncomeAttachment $attachment): void
    {
        $this->getEntityManager()->persist($attachment);
    }

    public function findForIncome(FiscalEntity $fiscalEntity, Income $income, string $id): ?IncomeAttachment
    {
        $attachment = $this->createQueryBuilder('a')
            ->andWhere('a.id = :id')
            ->andWhere('a.income = :income')
            ->andWhere('a.fiscalEntity = :entity')
            ->setParameter('id', $id)
            ->setParameter('income', $income)
            ->setParameter('entity', $fiscalEntity)
            ->getQuery()
            ->getOneOrNullResult();

        return $attachment instanceof IncomeAttachment ? $attachment : null;
    }

    public function forIncome(Income $income): array
    {
        $rows = $this->createQueryBuilder('a')
            ->andWhere('a.income = :income')
            ->andWhere('a.fiscalEntity = :entity')
            ->setParameter('income', $income)
            ->setParameter('entity', $income->fiscalEntity())
            ->orderBy('a.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
        $attachments = [];
        if (!is_array($rows)) {
            return $attachments;
        }
        foreach ($rows as $row) {
            if ($row instanceof IncomeAttachment) {
                $attachments[] = $row;
            }
        }

        return $attachments;
    }
}
