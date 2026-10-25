<?php

declare(strict_types=1);

namespace App\Identity;

use App\Entity\EmailConfirmation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailConfirmation>
 */
final class DoctrineEmailConfirmationRepository extends ServiceEntityRepository implements EmailConfirmationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailConfirmation::class);
    }

    public function findByTokenHash(string $tokenHash): ?EmailConfirmation
    {
        return $this->findOneBy(['tokenHash' => $tokenHash]);
    }

    public function save(EmailConfirmation $confirmation): void
    {
        $this->getEntityManager()->persist($confirmation);
    }
}
