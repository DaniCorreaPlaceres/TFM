<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\Account;
use App\Entity\PasswordReset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PasswordReset>
 */
final class DoctrinePasswordResetRepository extends ServiceEntityRepository implements PasswordResetRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordReset::class);
    }

    public function findByTokenHash(string $tokenHash): ?PasswordReset
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function findByAccount(Account $account): array
    {
        return $this->findBy(['account' => $account]);
    }

    public function save(PasswordReset $reset): void
    {
        $this->getEntityManager()->persist($reset);
    }
}
