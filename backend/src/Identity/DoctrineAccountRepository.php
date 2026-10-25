<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Account>
 */
final class DoctrineAccountRepository extends ServiceEntityRepository implements AccountRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Account::class);
    }

    public function findByEmail(string $email): ?Account
    {
        return $this->findOneBy(['email' => $email]);
    }

    public function save(Account $account): void
    {
        $this->getEntityManager()->persist($account);
    }
}
