<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\FiscalEntityMembership;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FiscalEntityMembership>
 */
final class DoctrineFiscalEntityMembershipRepository extends ServiceEntityRepository implements FiscalEntityMembershipRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FiscalEntityMembership::class);
    }

    public function save(FiscalEntityMembership $membership): void
    {
        $this->getEntityManager()->persist($membership);
    }

    public function findForAccountAndEntity(Account $account, string $fiscalEntityId): ?FiscalEntityMembership
    {
        return $this->findOneBy([
            'account' => $account,
            'fiscalEntity' => $fiscalEntityId,
        ]);
    }

    public function findForAccount(Account $account): array
    {
        return $this->findBy(['account' => $account]);
    }
}
