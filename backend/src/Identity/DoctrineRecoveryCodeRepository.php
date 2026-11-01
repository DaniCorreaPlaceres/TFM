<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\RecoveryCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RecoveryCode>
 */
final class DoctrineRecoveryCodeRepository extends ServiceEntityRepository implements RecoveryCodeRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RecoveryCode::class);
    }

    public function save(RecoveryCode $code): void
    {
        $this->getEntityManager()->persist($code);
    }

    public function findUsable(Account $account, string $codeHash): ?RecoveryCode
    {
        return $this->findOneBy([
            'account' => $account,
            'codeHash' => $codeHash,
            'usedAt' => null,
        ]);
    }

    public function deleteFor(Account $account): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->delete(RecoveryCode::class, 'code')
            ->where('code.account = :account')
            ->setParameter('account', $account)
            ->getQuery()
            ->execute();
    }
}
